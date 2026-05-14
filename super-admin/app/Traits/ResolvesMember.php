<?php

namespace App\Traits;

use App\Models\Member;

trait ResolvesMember
{
    protected function resolveMember(string $userId, ?string $memberId = null, ?string $accountId = null): ?Member
    {
        $member = Member::where('user_id', $userId)->first();

        if (! $member) {
            return null;
        }

        if ($memberId !== null && $member->id !== $memberId) {
            return null;
        }

        if ($accountId !== null) {
            $account = $member->account;
            if (! $account || $account->account_id !== $accountId) {
                return null;
            }
        }

        return $member;
    }

    protected function resolveAdminId(string $userId, ?string $memberId = null, ?string $accountId = null): ?string
    {
        $member = $this->resolveMember($userId, $memberId, $accountId);

        return $member?->admin_id;
    }
}
