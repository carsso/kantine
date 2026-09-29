<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\InteractsWithCanteen;
use App\Models\Dish;
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

#[Name('search-dishes')]
#[Description('Searches the dishes served, past and upcoming, and returns the dates they are served on. Dish names are in French. Use it to answer questions like "when are fries served next?" (query "frites", start_date today) or "when did we last have couscous?" (order desc, end_date today).')]
#[Title('Retrouver les dates où un plat est servi, avec filtres par tags et par dates')]
#[IsReadOnly]
class SearchDishesTool extends Tool
{
    use InteractsWithCanteen;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request, MenuSearchService $menuSearchService): ResponseFactory
    {
        $validated = $request->validate(MenuSearchService::searchRules());

        $canteen = $this->canteen();

        return Response::structured([
            'canteen' => $canteen->name,
            ...$menuSearchService->searchDishes($canteen, $validated),
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
            'query' => $schema->string()
                ->description('Words the dish name must all contain, case-insensitive (e.g. "frites", "poulet curry"). Omit to match every dish.'),
            'tags' => $schema->array()
                ->items($schema->string()->enum(array_keys(Dish::getTagsDefinitions())))
                ->description('Only keep dishes carrying all of these tags.'),
            'start_date' => $schema->string()->format('date')
                ->description('Only dishes served on or after this date, as YYYY-MM-DD.'),
            'end_date' => $schema->string()->format('date')
                ->description('Only dishes served on or before this date, as YYYY-MM-DD.'),
            'order' => $schema->string()->enum(['asc', 'desc'])
                ->description('Date order of the results.')
                ->default('asc'),
            'limit' => $schema->integer()->min(1)->max(MenuSearchService::MAX_SEARCH_RESULTS)
                ->description('Maximum number of results; "total" in the response gives the full match count.')
                ->default(MenuSearchService::DEFAULT_SEARCH_RESULTS),
        ];
    }
}
