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

#[Name('get-menus')]
#[Description('Gets the full menu for one day (today by default) or a range of up to '.MenuSearchService::MAX_MENU_DAYS.' days, e.g. a whole week. Days without any menu nor announcement are omitted.')]
#[Title('Le menu d\'un jour ou d\'une période (jusqu\'à '.MenuSearchService::MAX_MENU_DAYS.' jours)')]
#[IsReadOnly]
class GetMenusTool extends Tool
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
            'today' => today()->format('Y-m-d'),
            'days' => $menuSearchService->menus($canteen, $validated['start_date'] ?? null, $validated['end_date'] ?? null),
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
                ->description('Last day (included), as YYYY-MM-DD. Defaults to start_date.'),
        ];
    }
}
