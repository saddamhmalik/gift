<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $avatarUrl = null;
        if ($this->avatar) {
            // If it's already a full URL (Google, etc.) keep it; otherwise build storage URL
            $avatarUrl = str_starts_with($this->avatar, 'http')
                ? $this->avatar
                : asset('storage/' . $this->avatar);
        }

        return [
            'id'                  => $this->id,
            'name'                => $this->full_name,
            'first_name'          => $this->first_name,
            'last_name'           => $this->last_name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'avatar'              => $avatarUrl,
            'email_verified'      => ! is_null($this->email_verified_at),
            'phone_verified'      => ! is_null($this->phone_verified_at),
            'pending_email'       => $this->pending_email,
            'member_since'        => $this->created_at?->toDateString(),
        ];
    }
}
