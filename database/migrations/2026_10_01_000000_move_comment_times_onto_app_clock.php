<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Comments used to be stamped by the column's CURRENT_TIMESTAMP default, on
     * the database clock. Where that clock runs in another zone than the app
     * (a UTC database under an Asia/Manila app), every existing comment reads
     * that many hours old. Shift them onto the app's clock, as new comments now
     * are. Where both clocks already agree the gap is zero and nothing changes.
     */
    public function up(): void
    {
        $dbNow = Carbon::parse(DB::selectOne('select CURRENT_TIMESTAMP as db_now')->db_now);

        // Rounded to the quarter hour: zones are offset in those steps, and it
        // absorbs the moment that passes between the two clock reads.
        $shift = (int) (round($dbNow->diffInSeconds(now()) / 900) * 900);

        if ($shift === 0) {
            return;
        }

        foreach (['announcement_comments', 'proposal_comments'] as $table) {
            DB::table($table)->whereNotNull('created_at')->chunkById(200, function ($rows) use ($table, $shift) {
                foreach ($rows as $row) {
                    DB::table($table)->where('id', $row->id)->update([
                        'created_at' => Carbon::parse($row->created_at)->addSeconds($shift)->format('Y-m-d H:i:s'),
                    ]);
                }
            });
        }
    }

    /**
     * Not reversed: the gap this corrected is not recorded, and the times it
     * leaves are the right ones.
     */
    public function down(): void
    {
    }
};
