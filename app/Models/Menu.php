<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'event_name',
        'starters',
        'mains',
        'sides',
        'cheeses',
        'desserts',
        'file_id',
        'information',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'date_carbon' => 'date:Y-m-d',
        'starters' => 'array',
        'mains' => 'array',
        'sides' => 'array',
        'cheeses' => 'array',
        'desserts' => 'array',
        'is_fries_day' => 'boolean',
        'is_burgers_day' => 'boolean',
        'is_antioxidants_day' => 'boolean',
        'starters_without_usual' => 'array',
        'starters_usual' => 'array',
        'cheeses_without_usual' => 'array',
        'cheeses_usual' => 'array',
        'desserts_without_usual' => 'array',
        'desserts_usual' => 'array',
        'mains_special_indexes' => 'array',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['id', 'file_id'];

    /**
     * The attributes that should be appended to arrays.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_fries_day',
        'is_burgers_day',
        'is_antioxidants_day',
        'starters_without_usual',
        'starters_usual',
        'cheeses_without_usual',
        'cheeses_usual',
        'desserts_without_usual',
        'desserts_usual',
        'mains_special_indexes',
        'information_html',
    ];

    public function getIsAntioxidantsDayAttribute()
    {
        if (!$this->sides) {
            return false;
        }
        $needles = ['Haricots rouges', 'Lentilles'];
        foreach ($needles as $needle){
            if (str_contains(join(', ', $this->sides), $needle)) {
                return true;
            }
        }
        return false;
    }

    public function getNextAntioxidantsDayAttribute()
    {
        $menus = Menu::where('date', '>', $this->date)->orderBy('date', 'asc')->limit(30)->get();
        foreach ($menus as $menu) {
            if ($menu->is_antioxidants_day) {
                return $menu;
            }
        }
        return null;
    }

    public function getIsBurgersDayAttribute()
    {
        if (!$this->mains) {
            return false;
        }
        foreach ($this->mains as $idx => $dish) {
            if($this->getMainSpecialName($idx, false)) {
                # skip special dishes
                continue;
            }
            if (str_contains($dish, 'Burger')) {
                return true;
            }
        }
        return false;
    }

    public function getNextBurgersDayAttribute()
    {
        $menus = Menu::where('date', '>', $this->date)->orderBy('date', 'asc')->limit(30)->get();
        foreach ($menus as $menu) {
            if ($menu->is_burgers_day) {
                return $menu;
            }
        }
        return null;
    }

    public function getIsFriesDayAttribute()
    {
        if (!$this->sides) {
            return false;
        }
        return str_contains(join(', ', $this->sides), 'Frites');
    }

    public function getNextFriesDayAttribute()
    {
        $menus = Menu::where('date', '>', $this->date)->orderBy('date', 'asc')->limit(30)->get();
        foreach ($menus as $menu) {
            if ($menu->is_fries_day) {
                return $menu;
            }
        }
        return null;
    }

    public function getNextEventAttribute()
    {
        $menus = Menu::where('date', '>', $this->date)->orderBy('date', 'asc')->limit(30)->get();
        foreach ($menus as $menu) {
            if ($menu->event_name) {
                return $menu;
            }
        }
        return null;
    }

    public function getDateCarbonAttribute()
    {
        return Carbon::parse($this->date);
    }

    public function getInformationHtmlAttribute()
    {
        return nl2br(e($this->information), false);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function getUsualStartersConfig()
    {
        return ['Salad\'bar'];
    }

    public function getStartersWithoutUsualAttribute()
    {
        return array_values(
            array_filter($this->starters, function ($starter) {
                return !in_array($starter, $this->getUsualStartersConfig());
            })
        );
    }

    public function getStartersUsualAttribute()
    {
        return array_values(
            array_filter($this->starters, function ($starter) {
                return in_array($starter, $this->getUsualStartersConfig());
            })
        );
    }

    public function getUsualCheesesConfig()
    {
        return ['Fromages/laitages', 'Fromages/Laitages'];
    }

    public function getCheesesWithoutUsualAttribute()
    {
        return array_values(
            array_filter($this->cheeses, function ($cheese) {
                return !in_array($cheese, $this->getUsualCheesesConfig());
            })
        );
    }
    public function getCheesesUsualAttribute()
    {
        return array_values(
            array_filter($this->cheeses, function ($cheese) {
                return in_array($cheese, $this->getUsualCheesesConfig());
            })
        );
    }

    public function getUsualDessertsConfig()
    {
        return ['Corbeille de fruits', 'Sweet\'bar', 'Glace'];
    }
    public function getDessertsWithoutUsualAttribute()
    {
        return array_values(
                array_filter($this->desserts, function ($dessert) {
                return !in_array($dessert, $this->getUsualDessertsConfig());
            })
        );
    }
    public function getDessertsUsualAttribute()
    {
        return array_values(
            array_filter($this->desserts, function ($dessert) {
                return in_array($dessert, $this->getUsualDessertsConfig());
            })
        );
    }

    public function getMainsSpecialIndexesAttribute()
    {
        # return associative array with hallal for last and vegetarian for second to last
        return [
            'hallal' => count($this->mains) > 1 ? count($this->mains) - 1 : null,
            'vegetarian' => count($this->mains) > 2 ? count($this->mains) - 2 : null,
        ];
    }

    public function getMainSpecialName($index, $short = true)
    {
        if ($index === $this->mains_special_indexes['hallal']) {
            return 'Hallal';
        }
        if ($index === $this->mains_special_indexes['vegetarian']) {
            return $short ? 'Végé.' : 'Végétarien';
        }
        return null;
    }

}
