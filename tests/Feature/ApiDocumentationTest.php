<?php

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\ResponseFromTransformer;

/**
 * The SPA's TypeScript types are generated from the OpenAPI spec Scribe emits,
 * so an endpoint with no documented response generates as untyped and hands
 * the frontend an unsafe client. Fail the build here instead.
 */
const RESPONSE_ATTRIBUTES = [
    Response::class,
    ResponseFromApiResource::class,
    ResponseFromFile::class,
    ResponseFromTransformer::class,
];

/**
 * @return list<RoutingRoute>
 */
function documentedApiRoutes(): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->filter(fn (RoutingRoute $route) => str_starts_with($route->uri(), 'api/'))
        ->filter(fn (RoutingRoute $route) => is_string($route->getAction('controller')))
        ->values()
        ->all();
}

/**
 * The statuses this route documents a response body for.
 *
 * @return list<int>
 */
function documentedResponseStatusesFor(RoutingRoute $route): array
{
    [$class, $method] = explode('@', $route->getAction('controller').'@__invoke');

    $reflection = new ReflectionMethod($class, $method);

    $attributes = array_merge(
        $reflection->getAttributes(),
        $reflection->getDeclaringClass()->getAttributes(),
    );

    return collect($attributes)
        ->filter(fn (ReflectionAttribute $attribute) => in_array($attribute->getName(), RESPONSE_ATTRIBUTES, true))
        ->map(fn (ReflectionAttribute $attribute) => $attribute->newInstance()->status)
        ->values()
        ->all();
}

test('every API route resolves to a controller action', function () {
    $withoutController = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (RoutingRoute $route) => str_starts_with($route->uri(), 'api/'))
        ->reject(fn (RoutingRoute $route) => is_string($route->getAction('controller')))
        ->map(fn (RoutingRoute $route) => $route->methods()[0].' '.$route->uri())
        ->all();

    expect($withoutController)->toBe([]);
});

test('every API controller declares a documented success response', function () {
    $undocumented = collect(documentedApiRoutes())
        ->reject(fn (RoutingRoute $route) => collect(documentedResponseStatusesFor($route))
            ->contains(fn (int $status) => $status >= 200 && $status < 300))
        ->map(fn (RoutingRoute $route) => $route->methods()[0].' '.$route->uri())
        ->values()
        ->all();

    expect($undocumented)->toBe([]);
});

test('every form request states its rules without a bound route model', function () {
    $failures = collect(File::allFiles(app_path('Http/Requests')))
        ->map(fn ($file) => 'App\\Http\\Requests\\'.str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname()))
        ->filter(fn (string $class) => is_subclass_of($class, FormRequest::class))
        ->reject(fn (string $class) => (new ReflectionClass($class))->isAbstract())
        ->mapWithKeys(function (string $class): array {
            try {
                (new $class())->rules();

                return [];
            } catch (Throwable $e) {
                return [$class => $e->getMessage()];
            }
        })
        ->all();

    expect($failures)->toBe([]);
});
