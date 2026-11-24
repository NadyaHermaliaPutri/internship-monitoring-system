<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaAssetsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * 1. File web app manifest tersedia, berformat JSON valid, dan memiliki konfigurasi PWA WANTRACE.
     */
    public function test_manifest_webmanifest_is_accessible_and_valid_json(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);

        $content = file_get_contents($manifestPath);
        $json = json_decode($content, true);

        $this->assertIsArray($json, 'manifest.webmanifest harus berupa JSON valid');
        $this->assertEquals('WANTRACE - Monitoring Magang PT WAN', $json['name'] ?? null);
        $this->assertEquals('WANTRACE', $json['short_name'] ?? null);
        $this->assertEquals('standalone', $json['display'] ?? null);
        $this->assertEquals('#015987', $json['theme_color'] ?? null);
        $this->assertNotEmpty($json['icons'] ?? []);

        // Verifikasi setiap ikon yang dideklarasikan di manifest ada di disk
        foreach ($json['icons'] as $icon) {
            $iconPath = public_path(ltrim($icon['src'], '/'));
            $this->assertFileExists($iconPath, "File ikon {$icon['src']} harus ada di direktori public/");
        }
    }

    /**
     * 2. Service worker (sw.js) dan halaman offline (offline.html) ada di public dan dapat diakses.
     */
    public function test_service_worker_and_offline_page_exist_and_accessible(): void
    {
        $swPath = public_path('sw.js');
        $offlinePath = public_path('offline.html');

        $this->assertFileExists($swPath);
        $this->assertFileExists($offlinePath);

        $swContent = file_get_contents($swPath);
        // Aturan keamanan PWA: tidak boleh cache request non-GET dan tidak boleh cache /media/
        $this->assertStringContainsString("request.method !== 'GET'", $swContent);
        $this->assertStringContainsString("startsWith('/media/')", $swContent);
        $this->assertStringContainsString('/offline.html', $swContent);

        // Akses via HTTP response
        $this->get('/sw.js')->assertOk();
        $this->get('/offline.html')->assertOk();
        $this->get('/manifest.webmanifest')->assertOk();
    }

    /**
     * 3. Ikon PWA 192x192 dan 512x512 tersedia di disk dengan ukuran piksel yang presisi.
     */
    public function test_pwa_icon_assets_exist_with_exact_dimensions(): void
    {
        $icon192 = public_path('icons/icon-192x192.png');
        $icon512 = public_path('icons/icon-512x512.png');

        $this->assertFileExists($icon192);
        $this->assertFileExists($icon512);

        $size192 = getimagesize($icon192);
        $this->assertNotFalse($size192);
        $this->assertEquals(192, $size192[0], 'Lebar ikon 192 harus tepat 192px');
        $this->assertEquals(192, $size192[1], 'Tinggi ikon 192 harus tepat 192px');
        $this->assertEquals('image/png', $size192['mime']);

        $size512 = getimagesize($icon512);
        $this->assertNotFalse($size512);
        $this->assertEquals(512, $size512[0], 'Lebar ikon 512 harus tepat 512px');
        $this->assertEquals(512, $size512[1], 'Tinggi ikon 512 harus tepat 512px');
        $this->assertEquals('image/png', $size512['mime']);
    }

    /**
     * 4. Precache URL di sw.js semuanya benar-benar ada di disk.
     */
    public function test_all_precached_urls_exist_in_public_folder(): void
    {
        $swContent = file_get_contents(public_path('sw.js'));
        preg_match('/const PRECACHE_URLS = \[(.*?)\];/s', $swContent, $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        preg_match_all("/'([^']+)'/", $matches[1], $urlMatches);
        $urls = $urlMatches[1] ?? [];
        $this->assertNotEmpty($urls);

        foreach ($urls as $url) {
            $filePath = public_path(ltrim($url, '/'));
            $this->assertFileExists($filePath, "Aset precache {$url} tidak ditemukan di disk public!");
        }
    }

    /**
     * 5. Root URL (start_url PWA: "/") mengarahkan user terotentikasi langsung ke dashboard sesuai role
     * dan tamu (unauthenticated) diarahkan ke /login untuk mencegah ERR_TOO_MANY_REDIRECTS.
     */
    public function test_root_url_redirects_authenticated_users_to_their_dashboard_and_guests_to_login(): void
    {
        // A. Tamu (guest): diarahkan ke halaman login
        $responseGuest = $this->get('/');
        $responseGuest->assertRedirect(route('login'));

        // B. Admin: langsung ke dashboard admin
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $responseAdmin = $this->actingAs($admin)->get('/');
        $responseAdmin->assertRedirect(route('admin.dashboard'));

        // C. PIC: langsung ke dashboard pic
        $pic = User::factory()->create(['role' => 'pic', 'is_active' => true]);
        $responsePic = $this->actingAs($pic)->get('/');
        $responsePic->assertRedirect(route('pic.dashboard'));

        // D. Intern: langsung ke dashboard intern
        $intern = User::factory()->create(['role' => 'intern', 'is_active' => true]);
        $responseIntern = $this->actingAs($intern)->get('/');
        $responseIntern->assertRedirect(route('intern.dashboard'));

        // E. Pengguna nonaktif: otomatis logout dan diarahkan ke login dengan pesan kesalahan
        $inactive = User::factory()->create(['role' => 'intern', 'is_active' => false]);
        $responseInactive = $this->actingAs($inactive)->get('/');
        $responseInactive->assertRedirect(route('login'));
        $this->assertGuest();

        // F. Pengguna terotentikasi yang mengakses /login diarahkan oleh middleware guest ke /
        // yang kemudian meneruskan ke dashboard role masing-masing (tidak terjadi infinite loop)
        $this->actingAs($admin)->get('/login')->assertRedirect('/');
    }
}
