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
}
