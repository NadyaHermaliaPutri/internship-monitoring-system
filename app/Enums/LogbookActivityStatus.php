<?php

namespace App\Enums;

enum LogbookActivityStatus: string
{
    case BaruDikasih = 'baru_dikasih';
    case SedangDikerjakan = 'sedang_dikerjakan';
    case MenungguReview = 'menunggu_review';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::BaruDikasih => 'Baru Diberikan',
            self::SedangDikerjakan => 'Sedang Dikerjakan',
            self::MenungguReview => 'Menunggu Review',
            self::Selesai => 'Selesai',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::BaruDikasih => 'bg-slate-100 text-slate-700 border-slate-200',
            self::SedangDikerjakan => 'bg-sky-50 text-sky-700 border-sky-200',
            self::MenungguReview => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Selesai => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
