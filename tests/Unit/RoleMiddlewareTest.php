<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\ContentAdminMiddleware;
use App\Http\Middleware\EventAdminMiddleware;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_middleware_allows_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);
        
        $middleware = new SuperAdminMiddleware();
        $request = Request::create('/test');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('success');
        });
        
        $this->assertEquals('success', $response->getContent());
    }

    public function test_super_admin_middleware_blocks_non_super_admin(): void
    {
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $this->actingAs($contentAdmin);
        
        $middleware = new SuperAdminMiddleware();
        $request = Request::create('/test');
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied. Super admin privileges required.');
        
        $middleware->handle($request, function ($req) {
            return new Response('success');
        });
    }

    public function test_super_admin_middleware_redirects_unauthenticated(): void
    {
        $middleware = new SuperAdminMiddleware();
        $request = Request::create('/test');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('success');
        });
        
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    public function test_content_admin_middleware_allows_content_managers(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);
        
        $middleware = new ContentAdminMiddleware();
        $request = Request::create('/test');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('success');
        });
        
        $this->assertEquals('success', $response->getContent());
        
        // Test with content admin
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $this->actingAs($contentAdmin);
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('success');
        });
        
        $this->assertEquals('success', $response->getContent());
    }

    public function test_content_admin_middleware_blocks_non_content_managers(): void
    {
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $this->actingAs($eventAdmin);
        
        $middleware = new ContentAdminMiddleware();
        $request = Request::create('/test');
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied. Content management privileges required.');
        
        $middleware->handle($request, function ($req) {
            return new Response('success');
        });
    }

    public function test_event_admin_middleware_allows_event_managers(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);
        
        $middleware = new EventAdminMiddleware();
        $request = Request::create('/test');
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('success');
        });
        
        $this->assertEquals('success', $response->getContent());
        
        // Test with event admin
        $eventAdmin = User::factory()->create(['role' => 'event_admin']);
        $this->actingAs($eventAdmin);
        
        $response = $middleware->handle($request, function ($req) {
            return new Response('success');
        });
        
        $this->assertEquals('success', $response->getContent());
    }

    public function test_event_admin_middleware_blocks_non_event_managers(): void
    {
        $contentAdmin = User::factory()->create(['role' => 'content_admin']);
        $this->actingAs($contentAdmin);
        
        $middleware = new EventAdminMiddleware();
        $request = Request::create('/test');
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied. Event management privileges required.');
        
        $middleware->handle($request, function ($req) {
            return new Response('success');
        });
    }

    public function test_admin_middleware_allows_all_admin_roles(): void
    {
        $middleware = new AdminMiddleware();
        $request = Request::create('/test');
        
        $roles = ['super_admin', 'content_admin', 'event_admin'];
        
        foreach ($roles as $role) {
            $admin = User::factory()->create(['role' => $role]);
            $this->actingAs($admin);
            
            $response = $middleware->handle($request, function ($req) {
                return new Response('success');
            });
            
            $this->assertEquals('success', $response->getContent());
        }
    }

    public function test_admin_middleware_blocks_members(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $this->actingAs($member);
        
        $middleware = new AdminMiddleware();
        $request = Request::create('/test');
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied. Admin privileges required.');
        
        $middleware->handle($request, function ($req) {
            return new Response('success');
        });
    }

    public function test_all_middleware_redirect_unauthenticated_users(): void
    {
        $middlewares = [
            new SuperAdminMiddleware(),
            new ContentAdminMiddleware(),
            new EventAdminMiddleware(),
            new AdminMiddleware(),
        ];
        
        foreach ($middlewares as $middleware) {
            $request = Request::create('/test');
            
            $response = $middleware->handle($request, function ($req) {
                return new Response('success');
            });
            
            $this->assertEquals(302, $response->getStatusCode());
            $this->assertStringContainsString('login', $response->headers->get('Location'));
        }
    }
}
