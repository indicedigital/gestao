<?php

namespace App\Services;

use App\Models\User;

class MentionParserService
{
    /**
     * @return array{body: string, mentions: array<int, array{id: int, name: string}>}
     */
    public function parse(string $body, int $companyId): array
    {
        $mentions = [];
        $pattern = '/@([\p{L}\p{M}\.\-\']+(?:\s+[\p{L}\p{M}\.\-\']+)?)/u';

        preg_match_all($pattern, $body, $matches);

        foreach (array_unique($matches[1] ?? []) as $name) {
            $user = User::whereHas('companies', function ($q) use ($companyId) {
                $q->where('companies.id', $companyId)->wherePivot('is_active', true);
            })->where('name', 'like', trim($name).'%')->first();

            if ($user) {
                $mentions[] = ['id' => $user->id, 'name' => $user->name];
            }
        }

        return ['body' => $body, 'mentions' => $mentions];
    }
}
