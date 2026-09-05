<?php

namespace Tests\Feature;

use App\Helpers\SscHelper;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ActivityLogIpTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_captures_accurate_client_ip_when_behind_trusted_proxy(): void
    {
        // 1. Define a temporary route to invoke SscHelper::logActivity and check behavior
        Route::get('/_test_ip_resolution', function () {
            SscHelper::logActivity(null, 'TEST_IP_RESOLUTION', 'Testing proxy IP logging');
            return response('OK');
        });

        // 2. Perform the request, sending the X-Forwarded-For header representing the actual client
        $realClientIp = '203.0.113.195';
        $proxyIp = '10.0.0.1';

        $response = $this->withHeaders([
            'X-Forwarded-For' => $realClientIp,
        ])->withServerVariables([
            'REMOTE_ADDR' => $proxyIp,
        ])->get('/_test_ip_resolution');

        $response->assertStatus(200);

        // 3. Verify that the recorded ActivityLog contains the correct real client IP
        $log = ActivityLog::where('action', 'TEST_IP_RESOLUTION')->first();

        $this->assertNotNull($log, 'Activity log entry was not created.');
        $this->assertEquals($realClientIp, $log->ip_address, 'The logged IP address did not match the forwarded client IP.');
    }

    public function test_ip_address_is_resolved_correctly_on_requests(): void
    {
        // 1. Define a temporary route that returns the resolved IP address
        Route::get('/_test_ip_request', function () {
            return response()->json(['ip' => request()->ip()]);
        });

        $realClientIp = '198.51.100.42';
        $proxyIp = '10.0.0.2';

        $response = $this->withHeaders([
            'X-Forwarded-For' => $realClientIp,
        ])->withServerVariables([
            'REMOTE_ADDR' => $proxyIp,
        ])->get('/_test_ip_request');

        $response->assertStatus(200);
        $response->assertJson(['ip' => $realClientIp]);
    }

    public function test_format_log_details_converts_coordinates_to_google_maps_links(): void
    {
        // 1. Test standard login detail format
        $detail1 = "Logged in via admin portal | Location: Lat 14.5995, Lng 120.9842 | UA: Mozilla/5.0";
        $formatted1 = SscHelper::formatLogDetails($detail1);
        $this->assertStringContainsString('https://www.google.com/maps?q=14.5995%2C120.9842', $formatted1);
        $this->assertStringContainsString('target="_blank"', $formatted1);
        $this->assertStringContainsString('Lat 14.5995, Lng 120.9842', $formatted1);

        // 2. Test blocked geo spoof/login format with parentheses and colons
        $detail2 = "Access denied: Login attempt from outside the Philippines (Lat: 35.6762, Lng: 139.6503) for email: test@example.com";
        $formatted2 = SscHelper::formatLogDetails($detail2);
        $this->assertStringContainsString('https://www.google.com/maps?q=35.6762%2C139.6503', $formatted2);

        // 3. Test negative coordinates
        $detail3 = "Location: Lat -33.8688, Lng 151.2093";
        $formatted3 = SscHelper::formatLogDetails($detail3);
        $this->assertStringContainsString('https://www.google.com/maps?q=-33.8688%2C151.2093', $formatted3);

        // 4. Test normal logs without coordinates
        $detail4 = "Student submitted feedback";
        $formatted4 = SscHelper::formatLogDetails($detail4);
        $this->assertEquals('Student submitted feedback', $formatted4);
        $this->assertStringNotContainsString('google.com/maps', $formatted4);

        // 5. Test empty details
        $formatted5 = SscHelper::formatLogDetails(null);
        $this->assertStringContainsString('—', $formatted5);
    }

    public function test_activity_log_records_accurate_timestamp_in_configured_timezone(): void
    {
        $this->assertEquals('Asia/Manila', config('app.timezone'));

        $before = now();
        SscHelper::logActivity(null, 'TEST_TIMEZONE', 'Checking log time accuracy');
        $after = now();

        $log = ActivityLog::where('action', 'TEST_TIMEZONE')->first();

        $this->assertNotNull($log, 'Activity log entry was not created.');
        $this->assertNotNull($log->created_at, 'Activity log created_at timestamp is null.');
        $this->assertEquals('+08:00', $log->created_at->format('P'), 'Timezone offset should be +08:00 (Asia/Manila).');
        $this->assertTrue($log->created_at->greaterThanOrEqualTo($before->subSecond()));
        $this->assertTrue($log->created_at->lessThanOrEqualTo($after->addSecond()));
    }
}
