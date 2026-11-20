<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreTaskSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Task|null $task */
        $task = $this->route('task');

        return $task && ($this->user()?->can('submit', $task) ?? false);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var array<int, string> $allowedMimes */
        $allowedMimes = config('wantrace.uploads.task_evidence.mimes', [
            'mp4', 'webm', 'mov', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', 'zip', 'rar',
        ]);
        /** @var array<int, string> $videoMimes */
        $videoMimes = config('wantrace.uploads.task_evidence.video_mimes', ['mp4', 'webm', 'mov']);
        $maxVideoKb = (int) config('wantrace.uploads.task_evidence.max_video_size_kb', 15360);
        $maxDocKb = (int) config('wantrace.uploads.task_evidence.max_document_size_kb', 10240);

        return [
            'submission_url' => [
                'nullable',
                'required_without:evidence_file',
                'string',
                'max:2048',
                'url:http,https',
            ],
            'evidence_file' => [
                'nullable',
                'required_without:submission_url',
                'file',
                'mimes:'.implode(',', $allowedMimes),
                'max:'.$maxVideoKb,
                function (string $attribute, mixed $value, Closure $fail) use ($videoMimes, $maxVideoKb, $maxDocKb) {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }

                    $ext = strtolower($value->getClientOriginalExtension());
                    $mime = strtolower((string) $value->getMimeType());
                    $sizeKb = ($value->getSize() ?: 0) / 1024;

                    $isVideo = in_array($ext, $videoMimes, true) || str_starts_with($mime, 'video/');

                    if ($isVideo && $sizeKb > $maxVideoKb) {
                        $fail('Ukuran berkas video maksimal adalah 15 MB.');
                    } elseif (! $isVideo && $sizeKb > $maxDocKb) {
                        $fail('Ukuran berkas dokumen/gambar/arsip maksimal adalah 10 MB.');
                    }
                },
            ],
            'submission_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'submission_url.required_without' => 'Minimal salah satu bukti tugas (tautan URL atau berkas/video) wajib diisi.',
            'submission_url.url' => 'Tautan bukti harus berupa URL yang valid (diawali http:// atau https://).',
            'submission_url.max' => 'Tautan URL maksimal 2048 karakter.',
            'evidence_file.required_without' => 'Minimal salah satu bukti tugas (berkas/video atau tautan URL) wajib diunggah.',
            'evidence_file.file' => 'Bukti unggahan harus berupa berkas yang valid.',
            'evidence_file.mimes' => 'Format berkas tidak didukung. Gunakan MP4/WEBM/MOV, PDF, Office, Gambar (JPG/PNG/WEBP), atau ZIP/RAR.',
            'evidence_file.max' => 'Ukuran berkas melebihi batas maksimal 15 MB.',
            'submission_note.max' => 'Catatan pengumpulan maksimal 2000 karakter.',
        ];
    }
}
