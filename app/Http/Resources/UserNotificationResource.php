<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subject' => $this->subject,
            'body' => $this->body,
            'table_head' => json_decode($this->table_head),
            'table_body' => json_decode($this->table_body),
            'is_read' => $this->is_read,
            'date' => Carbon::parse($this->created_at)->toDateString(),
        ];
    }
}
