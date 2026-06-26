<?php

namespace Modules\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'dailyEngagementId' => $this->dailyEngagementId,
            'punchedAt'         => $this->punched_at,
            'type'              => $this->type,
            'source'            => $this->source,
            'note'              => $this->note,
        ];
    }
}
