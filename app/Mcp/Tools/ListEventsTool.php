<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithCanteen;
use App\Services\MenuSearchService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list-events')]
#[Description('Lists the special events (themed meals, holidays...) and announcements, upcoming ones by default.')]
#[Title('Les événements et annonces à venir')]
#[IsReadOnly]
class ListEventsTool extends Tool
{
    use InteractsWithCanteen;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, MenuSearchService $menuSearchService): ResponseFactory
    {
        $validated = $request->validate(MenuSearchService::dateRangeRules());

        $canteen = $this->canteen();

        return Response::structured([
            'canteen' => $canteen->name,
            'events' => $menuSearchService->events($canteen, $validated['start_date'] ?? null, $validated['end_date'] ?? null),
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string()->format('date')
                ->description('First day, as YYYY-MM-DD. Defaults to today.'),
            'end_date' => $schema->string()->format('date')
                ->description('Last day (included), as YYYY-MM-DD. No limit by default.'),
        ];
    }
}
