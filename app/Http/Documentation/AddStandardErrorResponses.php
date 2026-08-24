<?php

namespace App\Http\Documentation;

use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\Strategies\Strategy;

/**
 * The error bodies every client branches on, documented once.
 *
 * The SPA's types are generated from the OpenAPI spec, so an undocumented 401
 * or 422 leaves the client guessing at a body it already handles. These two
 * shapes are the framework's, identical on every endpoint that can return
 * them, so they are added here rather than repeated as attributes on sixty
 * controllers. An endpoint that documents its own version of a status keeps
 * it: this only fills gaps.
 */
class AddStandardErrorResponses extends Strategy
{
    /**
     * @param  array<string, mixed>  $settings
     * @return list<array{status: int, content: string, description: string}>
     */
    public function __invoke(ExtractedEndpointData $endpointData, array $settings = []): ?array
    {
        $responses = [];

        if ($endpointData->metadata->authenticated && ! $this->alreadyDocuments($endpointData, 401)) {
            $responses[] = [
                'status' => 401,
                'content' => (string) json_encode(['message' => 'Unauthenticated.']),
                'description' => 'The request carries no valid token.',
            ];
        }

        if ($this->validates($endpointData) && ! $this->alreadyDocuments($endpointData, 422)) {
            $responses[] = [
                'status' => 422,
                'content' => (string) json_encode([
                    'message' => 'The given data was invalid.',
                    'errors' => ['field_name' => ['The field name field is required.']],
                ]),
                'description' => 'The submitted data failed validation.',
            ];
        }

        return $responses;
    }

    private function alreadyDocuments(ExtractedEndpointData $endpointData, int $status): bool
    {
        return $endpointData->responses->contains(fn ($response): bool => $response->status === $status);
    }

    /**
     * Only endpoints that accept a body can fail validation.
     */
    private function validates(ExtractedEndpointData $endpointData): bool
    {
        return $endpointData->bodyParameters !== [];
    }
}
