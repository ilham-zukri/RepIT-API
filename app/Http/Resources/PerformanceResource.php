<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $month = $this->month_period;
        $monthName = date('F', mktime(0, 0, 0, $month, 10)); // convert month number to month name
        $period = $monthName . ' ' . $this->year_period;
        return [
            'id' => $this->id,
            'period' => $period,
            'total_tickets' => $this->total_tickets,
            'tickets_meeting_requirements' => $this->tickets_meeting_requirements,
            'sla' => $this->sla,
        ];
    }
}
