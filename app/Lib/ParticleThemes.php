<?php

namespace App\Lib;

/**
 * Dashboard themes (tsparticles options), built from reusable layers and behaviours.
 *
 * Every layer is a particle group, so behaviours never leak from one layer to
 * another. Icons are Font Awesome Pro solid glyphs drawn through the text shape.
 * Static opacity goes through paint.fill.opacity (see config/tsparticles.php);
 * animated opacity has to use particles.opacity and ends up squared, which only
 * makes the fade deeper.
 */
class ParticleThemes
{
    private const FONT_AWESOME_FAMILY = 'Font Awesome 7 Pro';

    /**
     * Above this radius a particle is big enough to blur the menu text behind it.
     */
    private const SMALL_PARTICLE_MAX_SIZE = 5;

    /**
     * Big particles stay in the background so the menu remains readable,
     * only small ones (confetti, sparkles, embers) are drawn vividly.
     */
    private const BACKGROUND_OPACITY = ['min' => 0.3, 'max' => 0.5];

    private const SMALL_PARTICLE_OPACITY = ['min' => 0.6, 'max' => 1];

    public const CATEGORY_FESTIVITIES = 'Fêtes';

    public const CATEGORY_SEASONS = 'Saisons et météo';

    public const CATEGORY_CAUSES = 'Journées et causes';

    public const CATEGORY_SPORT = 'Sport';

    public const CATEGORY_FOOD = 'Cuisine';

    public const CATEGORY_REGIONS = 'Régions de France';

    public const CATEGORY_TRAVEL = 'Voyages et cuisines du monde';

    public const CATEGORY_COMPANY = 'Entreprise';

    public const CATEGORY_DECORATIONS = 'Décors';

    /**
     * Order of the categories in the admin.
     */
    public const CATEGORIES = [
        self::CATEGORY_FESTIVITIES,
        self::CATEGORY_SEASONS,
        self::CATEGORY_CAUSES,
        self::CATEGORY_SPORT,
        self::CATEGORY_FOOD,
        self::CATEGORY_REGIONS,
        self::CATEGORY_TRAVEL,
        self::CATEGORY_COMPANY,
        self::CATEGORY_DECORATIONS,
    ];

    /**
     * Font Awesome codepoints, from node_modules/@fortawesome/fontawesome-pro/css/all.css.
     */
    private const ICONS = [
        'acorn' => "\u{f6ae}",
        'anchor' => "\u{f13d}",
        'apple-whole' => "\u{f5d1}",
        'avocado' => "\u{e0aa}",
        'backpack' => "\u{f5d4}",
        'bacon' => "\u{f7e5}",
        'baguette' => "\u{e3d8}",
        'balloon' => "\u{e2e3}",
        'ban-smoking' => "\u{f54d}",
        'banana' => "\u{e2e5}",
        'bat' => "\u{f6b5}",
        'beer-mug' => "\u{e0b3}",
        'bell' => "\u{f0f3}",
        'bicycle' => "\u{f206}",
        'bird' => "\u{e469}",
        'blueberries' => "\u{e2e8}",
        'book' => "\u{f02d}",
        'bowl-chopsticks-noodles' => "\u{e2ea}",
        'bowl-food' => "\u{e4c6}",
        'bowl-hot' => "\u{f823}",
        'bowl-rice' => "\u{e2eb}",
        'brackets-curly' => "\u{f7ea}",
        'bread-slice' => "\u{f7ec}",
        'bug' => "\u{f188}",
        'burger' => "\u{f805}",
        'burrito' => "\u{f7ed}",
        'bus' => "\u{f207}",
        'butter' => "\u{e3e4}",
        'butterfly' => "\u{e811}",
        'cake-candles' => "\u{f1fd}",
        'campfire' => "\u{f6ba}",
        'candle-holder' => "\u{f6bc}",
        'candy' => "\u{e3e7}",
        'candy-cane' => "\u{f786}",
        'carrot' => "\u{f787}",
        'chart-line-up' => "\u{e0e5}",
        'cheese' => "\u{f7ef}",
        'cheese-swiss' => "\u{f7f0}",
        'cherries' => "\u{e0ec}",
        'chopsticks' => "\u{e3f7}",
        'clover' => "\u{e139}",
        'coconut' => "\u{e2f6}",
        'code' => "\u{f121}",
        'coffee-bean' => "\u{e13e}",
        'coins' => "\u{f51e}",
        'cookie' => "\u{f563}",
        'corn' => "\u{f6c7}",
        'croissant' => "\u{f7f6}",
        'crown' => "\u{f521}",
        'cube' => "\u{f1b2}",
        'cup-straw' => "\u{e363}",
        'dragon' => "\u{f6d5}",
        'droplet' => "\u{f043}",
        'drum' => "\u{f569}",
        'drumstick-bite' => "\u{f6d7}",
        'duck' => "\u{f6d8}",
        'dumbbell' => "\u{f44b}",
        'earth-europe' => "\u{f7a2}",
        'egg' => "\u{f7fb}",
        'egg-fried' => "\u{f7fc}",
        'elephant' => "\u{f6da}",
        'fire-flame-simple' => "\u{f46a}",
        'fish' => "\u{f578}",
        'flag-checkered' => "\u{f11e}",
        'flower' => "\u{f7ff}",
        'flower-daffodil' => "\u{f800}",
        'flower-tulip' => "\u{f801}",
        'fondue-pot' => "\u{e40d}",
        'football' => "\u{f44e}",
        'french-fries' => "\u{f803}",
        'futbol' => "\u{f1e3}",
        'garlic' => "\u{e40e}",
        'ghost' => "\u{f6e2}",
        'gift' => "\u{f06b}",
        'gingerbread-man' => "\u{f79d}",
        'glass-water' => "\u{e4f4}",
        'grapes' => "\u{e306}",
        'guitar' => "\u{f7a6}",
        'hand-holding-heart' => "\u{f4be}",
        'handshake' => "\u{f2b5}",
        'handshake-angle' => "\u{f4c4}",
        'hat-chef' => "\u{f86b}",
        'heart' => "\u{f004}",
        'heart-pulse' => "\u{f21e}",
        'hotdog' => "\u{f80f}",
        'house-chimney' => "\u{e3af}",
        'house-heart' => "\u{f4c9}",
        'ice-cream' => "\u{f810}",
        'jack-o-lantern' => "\u{f30e}",
        'key' => "\u{f084}",
        'leaf' => "\u{f06c}",
        'leaf-maple' => "\u{f6f6}",
        'leaf-oak' => "\u{f6f7}",
        'lemon' => "\u{f094}",
        'lightbulb' => "\u{f0eb}",
        'lighthouse' => "\u{e612}",
        'lock' => "\u{f023}",
        'lungs' => "\u{f604}",
        'mango' => "\u{e30f}",
        'masks-theater' => "\u{f630}",
        'meat' => "\u{f814}",
        'medal' => "\u{f5a2}",
        'microphone' => "\u{f130}",
        'mountain' => "\u{f6fc}",
        'mountain-sun' => "\u{e52f}",
        'mug-hot' => "\u{f7b6}",
        'mug-saucer' => "\u{f0f4}",
        'mushroom' => "\u{e425}",
        'music' => "\u{f001}",
        'music-note' => "\u{f8cf}",
        'mustache' => "\u{e5bc}",
        'olive' => "\u{e316}",
        'onion' => "\u{e427}",
        'ornament' => "\u{f7b8}",
        'pancakes' => "\u{e42d}",
        'paper-plane' => "\u{f1d8}",
        'peach' => "\u{e20b}",
        'pear' => "\u{e20c}",
        'pencil' => "\u{f303}",
        'people-group' => "\u{e533}",
        'pepper-hot' => "\u{f816}",
        'person-biking' => "\u{f84a}",
        'person-running' => "\u{f70c}",
        'person-swimming' => "\u{f5c4}",
        'pineapple' => "\u{e31f}",
        'pizza' => "\u{f817}",
        'popcorn' => "\u{f819}",
        'potato' => "\u{e440}",
        'presentation-screen' => "\u{f685}",
        'pretzel' => "\u{e441}",
        'rabbit' => "\u{f708}",
        'racquet' => "\u{f45a}",
        'rainbow' => "\u{f75b}",
        'recycle' => "\u{f1b8}",
        'ribbon' => "\u{f4d6}",
        'ruler' => "\u{f545}",
        'sailboat' => "\u{e445}",
        'salad' => "\u{f81e}",
        'sandwich' => "\u{f81f}",
        'sausage' => "\u{f820}",
        'saxophone' => "\u{f8dc}",
        'scooter' => "\u{e7c3}",
        'seedling' => "\u{f4d8}",
        'shield-halved' => "\u{f3ed}",
        'shirt' => "\u{f553}",
        'shrimp' => "\u{e448}",
        'snowflake' => "\u{f2dc}",
        'sparkle' => "\u{e5d6}",
        'spider' => "\u{f717}",
        'star' => "\u{f005}",
        'steak' => "\u{f824}",
        'strawberry' => "\u{e32b}",
        'sun' => "\u{f185}",
        'sun-bright' => "\u{e28f}",
        'sushi' => "\u{e48a}",
        'taco' => "\u{f826}",
        'tag' => "\u{f02b}",
        'tennis-ball' => "\u{f45e}",
        'terminal' => "\u{f120}",
        'ticket' => "\u{f145}",
        'tomato' => "\u{e330}",
        'tree' => "\u{f1bb}",
        'tree-christmas' => "\u{f7db}",
        'tree-palm' => "\u{f82b}",
        'trophy' => "\u{f091}",
        'umbrella' => "\u{f0e9}",
        'umbrella-beach' => "\u{f5ca}",
        'universal-access' => "\u{f29a}",
        'utensils' => "\u{f2e7}",
        'venus' => "\u{f221}",
        'volcano' => "\u{f770}",
        'volleyball' => "\u{f45f}",
        'waffle' => "\u{e466}",
        'watermelon-slice' => "\u{e337}",
        'wheat' => "\u{f72d}",
    ];

    /**
     * Words of the event name that suggest a theme, without accents. Keywords of 3 letters
     * or less must match a whole word, longer ones also match the start of a word.
     *
     * @var array<string, list<string>>
     */
    private const KEYWORDS = [
        'new_year' => ['nouvel an', 'nouvelle annee', 'bonne annee', 'reveillon', 'saint sylvestre'],
        'galette' => ['galette des rois', 'epiphanie', 'frangipane'],
        'lunar_new_year' => ['nouvel an chinois', 'nouvel an lunaire', 'chine', 'chinois'],
        'valentine' => ['saint valentin', 'st valentin', 'valentin', 'amoureux'],
        'carnival' => ['mardi gras', 'carnaval'],
        'st_patrick' => ['saint patrick', 'st patrick', 'irlande', 'irlandais'],
        'spring' => ['printemps'],
        'april_fool' => ['poisson d avril', '1er avril'],
        'easter' => ['paques', 'lapin'],
        'may_day' => ['muguet', '1er mai', 'fete du travail', 'travailleurs'],
        'mothers_day' => ['fete des meres', 'maman'],
        'fathers_day' => ['fete des peres', 'papa'],
        'music_day' => ['fete de la musique', 'musique', 'concert'],
        'summer' => ['ete', 'plage'],
        'bastille_day' => ['14 juillet', 'fete nationale'],
        'tour_de_france' => ['tour de france', 'cyclisme'],
        'back_to_school' => ['rentree'],
        'braderie' => ['braderie', 'moules frites', 'moules'],
        'pink_october' => ['octobre rose', 'cancer du sein'],
        'halloween' => ['halloween', 'haloween', 'citrouille', 'sorciere'],
        'movember' => ['movember', 'moustache'],
        'st_nicolas' => ['saint nicolas', 'st nicolas', 'pain d epices'],
        'christmas_market' => ['marche de noel', 'vin chaud'],
        'christmas' => ['noel', 'christmas'],
        'winter' => ['hiver'],
        'autumn' => ['automne', 'champignon', 'chataigne', 'courge'],
        'rain' => ['pluie'],
        'heatwave' => ['canicule', 'chaleur'],
        'world_cup' => ['coupe du monde', 'euro de foot', 'foot'],
        'olympics' => ['jeux olympiques', 'jo', 'olympi', 'paralympi'],
        'rugby' => ['rugby', 'six nations', '6 nations'],
        'tennis' => ['roland garros', 'tennis'],
        'womens_day' => ['droits des femmes', 'journee de la femme', 'femme', '8 mars'],
        'neighbours_day' => ['fete des voisins', 'voisin'],
        'pride' => ['pride', 'fierte', 'lgbt'],
        'earth_day' => ['jour de la terre', 'developpement durable', 'planete', 'climat', 'ecolo'],
        'europe_day' => ['journee de l europe', 'europe', 'union europeenne'],
        'saint_jean' => ['saint jean', 'st jean', 'feux de la saint jean'],
        'telethon' => ['telethon', 'afm'],
        'orange_day' => ['violences faites aux femmes', 'violence a l egard des femmes', 'elimination de la violence', 'orange the world', '25 novembre'],
        'remembrance' => ['armistice', 'commemoration', 'victoire', '8 mai', '11 novembre', 'bleuet', 'coquelicot', 'souvenir'],
        'diwali' => ['diwali', 'deepavali', 'fete des lumieres', 'lumiere'],
        'crepes' => ['crepe', 'chandeleur'],
        'waffles' => ['gaufre'],
        'french_toast' => ['pain perdu', 'brioche perdue'],
        'croque_monsieur' => ['croque'],
        'ice_cream' => ['glace', 'sorbet'],
        'granita' => ['granite', 'granita'],
        'donuts' => ['beignet', 'donut', 'churros'],
        'fun_fair' => ['fete foraine', 'ducasse', 'kermesse', 'popcorn', 'barbe a papa'],
        'cookies' => ['cookie', 'biscuit'],
        'bubble_tea' => ['bubble tea'],
        'hot_chocolate' => ['chocolat chaud', 'gouter'],
        'wraps' => ['wrap'],
        'brunch' => ['brunch', 'petit dejeuner', 'breakfast'],
        'street_food' => ['street food', 'food truck', 'finger food', 'snacking'],
        'burger' => ['burger', 'hamburger', 'frites'],
        'hot_dog' => ['hot dog', 'hotdog'],
        'bbq' => ['bbq', 'barbecue', 'barbec', 'grillade', 'plancha', 'brochette'],
        'mountain' => ['tartiflette', 'raclette', 'fondue', 'savoie', 'savoyard', 'montagne', 'gratin', 'reblochon', 'croziflette', 'ski'],
        'soups' => ['soupe', 'pot au feu', 'potage', 'veloute'],
        'nord' => ['welsh', 'carbonnade', 'chti', 'ch ti', 'nord', 'flandre', 'potjevleesch', 'maroilles'],
        'healthy' => ['healthy', 'detox', 'equilibre', 'salade'],
        'italian' => ['ital', 'pasta', 'pates', 'pizza', 'sicile', 'risotto', 'lasagne'],
        'mexican' => ['mexi', 'tacos', 'tex mex', 'burrito'],
        'asian' => ['asie', 'asiat', 'wok', 'nem', 'dim sum'],
        'couscous' => ['couscous', 'tajine', 'maghreb', 'orient'],
        'seafood' => ['poisson', 'fruits de mer', 'crustace', 'maree'],
        'picnic' => ['pique nique', 'picnic'],
        'fruits' => ['fruit', 'vitamine'],
        'coffee' => ['cafe', 'coffee'],
        'bread' => ['fete du pain', 'boulangerie', 'pain'],
        'gastronomy' => ['gastronomie', 'gastronomique', 'chef'],
        'brittany' => ['bretagne', 'breton', 'kouign', 'cidre'],
        'alsace' => ['alsace', 'alsacien', 'choucroute', 'flammekueche', 'tarte flambee'],
        'south_west' => ['sud ouest', 'cassoulet', 'canard', 'basque', 'landais'],
        'provence' => ['provence', 'provencal', 'ratatouille', 'bouillabaisse', 'marseill'],
        'corsica' => ['corse'],
        'overseas' => ['antilles', 'antillais', 'reunion', 'creole', 'outre mer', 'martinique', 'guadeloupe', 'colombo', 'rougail', 'tropical', 'exotique'],
        'france' => ['france', 'french', 'francais', 'terroir', 'local', 'bistrot', 'tradition'],
        'spain' => ['espagn', 'tapas', 'paella'],
        'portugal' => ['portugal', 'portugais'],
        'germany' => ['allemagne', 'allemand'],
        'belgium' => ['belgique', 'belge'],
        'united_kingdom' => ['angleterre', 'anglais', 'british', 'royaume uni', 'ecosse'],
        'poland' => ['pologne', 'polonais'],
        'greece' => ['grece', 'grec', 'moussaka'],
        'india' => ['inde', 'indien', 'curry', 'tandoori'],
        'morocco' => ['maroc', 'marocain'],
        'lebanon' => ['liban', 'libanais', 'mezze'],
        'usa' => ['america', 'americain', 'usa', 'etats unis'],
        'louisiana' => ['louisiane', 'cajun', 'nouvelle orleans'],
        'brazil' => ['bresil'],
        'japan' => ['japon', 'japonais', 'sushi', 'ramen'],
        'thailand' => ['thai'],
        'vietnam' => ['vietnam', 'pho', 'bo bun'],
        'korea' => ['coree', 'coreen', 'kimchi', 'bibimbap'],
        'turkey' => ['turquie', 'turc', 'kebab'],
        'west_africa' => ['afrique', 'africain', 'senegal', 'mafe', 'yassa'],
        'peru' => ['perou', 'peruvien', 'ceviche'],
        'argentina' => ['argentin'],
        'hawaii' => ['hawai', 'poke'],
        'scandinavia' => ['scandinav', 'suede', 'norvege', 'danemark', 'nordique'],
        'canada' => ['canada', 'canadien', 'quebec', 'poutine'],
        'ai' => ['intelligence artificielle', 'ia', 'ai summit'],
        'hackathon' => ['hackathon', 'hack'],
        'anniversary' => ['anniversaire', 'birthday'],
        'housewarming' => ['cremaillere', 'inauguration', 'demenagement'],
        'veggie' => ['vegetarien', 'vegan', 'vege', 'sans viande', 'veggie'],
        'zero_waste' => ['dechet', 'anti gaspi', 'gaspillage'],
        'taste_week' => ['semaine du gout', 'gout', 'alimentation'],
        'cyber_month' => ['cyber', 'securite informatique', 'phishing'],
        'blood_donation' => ['don du sang', 'donneur de sang', 'collecte de sang', 'efs'],
        'no_tobacco' => ['sans tabac', 'tabac'],
        'health_week' => ['sante', 'sport', 'bien etre'],
        'mobility_week' => ['mobilite', 'covoiturage', 'velo'],
        'disability_week' => ['handicap', 'seeph', 'inclusion'],
        'seminar' => ['seminaire', 'convention', 'kick off', 'all hands'],
        'annual_results' => ['resultats', 'bilan annuel'],
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $themes = [
            ...self::festivities(),
            ...self::seasons(),
            ...self::causes(),
            ...self::sport(),
            ...self::food(),
            ...self::regions(),
            ...self::travel(),
            ...self::company(),
        ];

        foreach ($themes as $key => $theme) {
            $themes[$key]['keywords'] = self::KEYWORDS[$key] ?? [];
        }

        return $themes;
    }

    /**
     * Groups themes by category, in the order of CATEGORIES, keeping their order within a category.
     *
     * @param  array<string, array<string, mixed>>  $themes
     * @return array<string, array<string, mixed>>
     */
    public static function sortedByCategory(array $themes): array
    {
        $rank = array_flip(self::CATEGORIES);
        $keys = array_keys($themes);
        $position = array_flip($keys);
        usort($keys, fn (string $a, string $b): int => [$rank[$themes[$a]['category']] ?? PHP_INT_MAX, $position[$a]] <=> [$rank[$themes[$b]['category']] ?? PHP_INT_MAX, $position[$b]]);

        return array_combine($keys, array_map(fn (string $key): array => $themes[$key], $keys));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function festivities(): array
    {
        $category = self::CATEGORY_FESTIVITIES;

        return [
            'new_year' => self::theme('Nouvel an', $category, [
                self::layer(60, self::circle(), ['#FFD124', '#FFFFFF'], ['min' => 1, 'max' => 3], self::still(), self::twinkling()),
            ], self::fireworks(['#FFD124', '#FFFFFF', '#4AB0F5', '#F472B6'])),

            'galette' => self::theme('Galette des rois', $category, [
                self::layer(120, self::circle(), ['#FFD124', '#FDE68A'], ['min' => 1, 'max' => 2.5], self::wandering(0.3), self::twinkling()),
                self::layer(14, self::glyphs('crown'), ['#FFD124', '#F59E0B'], ['min' => 12, 'max' => 20], self::falling(0.6, 1.2), self::swaying(), self::flipping(2, 5, 'horizontal')),
            ]),

            'lunar_new_year' => self::theme('Nouvel an lunaire', $category, [
                self::layer(18, self::circle(), ['#EF4444', '#F97316', '#DC2626'], ['min' => 10, 'max' => 18], self::rising(0.4, 0.9), self::swaying(10, 2), self::fading(0.8, 0.5)),
                self::layer(16, self::circle(), '#FFD124', ['min' => 5, 'max' => 8], self::falling(1, 2), self::flipping(8, 14)),
                self::layer(3, self::glyphs('dragon'), '#FFD124', ['min' => 26, 'max' => 34], self::drifting('right', 0.6, 1), self::swaying(30, 2)),
            ]),

            'valentine' => self::theme('Saint-Valentin', $category, [
                self::layer(30, self::glyphs('heart'), ['#F43F5E', '#FB7185', '#FFFFFF', '#F9A8D4'], ['min' => 6, 'max' => 18], self::rising(0.5, 1.2), self::swaying(15, 3), self::pulsing(12)),
                self::layer(40, self::circle(), '#FBCFE8', ['min' => 1, 'max' => 2], self::still(), self::twinkling()),
            ]),

            'carnival' => self::theme('Mardi gras / Carnaval', $category, [
                self::layer(120, self::square(), ['#A6D64D', '#4AB0F5', '#ED733D', '#FFD124', '#F472B6', '#A78BFA'], ['min' => 3, 'max' => 6], self::falling(1, 2.5), self::fluttering()),
                self::layer(6, self::glyphs('masks-theater'), ['#FFD124', '#F472B6'], ['min' => 20, 'max' => 28], self::wandering(0.8), self::swaying(20, 2)),
            ]),

            'st_patrick' => self::theme('Saint-Patrick', $category, [
                self::layer(30, self::glyphs('clover'), ['#22C55E', '#16A34A', '#86EFAC'], ['min' => 10, 'max' => 18], self::falling(0.8, 1.6), self::swaying(25, 4), self::flipping(3, 6, 'horizontal')),
                self::layer(10, self::circle(), '#FFD124', ['min' => 5, 'max' => 8], self::hopping(2), self::flipping(10, 15)),
            ]),

            'april_fool' => self::theme('Poisson d\'avril', $category, [
                self::layer(16, self::glyphs('fish'), ['#F97316', '#FFD124', '#4AB0F5', '#F472B6', '#A6D64D'], ['min' => 10, 'max' => 20], self::drifting('right', 1, 2.5), self::swaying(30, 3)),
                self::layer(40, self::circle(), '#FFFFFF', ['min' => 2, 'max' => 6], self::rising(0.8, 1.6), self::swaying(10, 5), self::opacity(0.15, 0.35)),
            ]),

            'easter' => self::theme('Pâques', $category, [
                self::layer(6, self::glyphs('rabbit'), '#FFFFFF', ['min' => 14, 'max' => 20], self::hopping(2.5)),
                self::layer(22, self::glyphs('egg'), ['#FBCFE8', '#BFDBFE', '#FDE68A', '#BBF7D0', '#DDD6FE'], ['min' => 10, 'max' => 16], self::falling(0.6, 1.2), self::swaying(15, 3), self::spinning(4)),
                self::layer(10, self::glyphs('flower-tulip', 'flower-daffodil'), ['#F472B6', '#FFD124'], ['min' => 10, 'max' => 14], self::rising(0.3, 0.6), self::swaying(10, 2)),
            ]),

            'may_day' => self::theme('Journée internationale des travailleurs', $category, [
                self::layer(26, self::glyphs('bell'), '#FFFFFF', ['min' => 6, 'max' => 12], self::falling(0.4, 0.9), self::swaying(20, 3), self::flipping(2, 4, 'horizontal')),
                self::layer(18, self::glyphs('leaf'), ['#22C55E', '#86EFAC'], ['min' => 10, 'max' => 16], self::drifting('bottom-right', 0.6, 1.2), self::fluttering()),
            ]),

            'mothers_day' => self::theme('Fête des mères', $category, [
                self::layer(20, self::glyphs('flower', 'flower-tulip'), ['#F9A8D4', '#C4B5FD', '#FDA4AF'], ['min' => 10, 'max' => 18], self::rising(0.4, 0.8), self::swaying(20, 2), self::spinning(2)),
                self::layer(14, self::glyphs('heart'), ['#FB7185', '#FFFFFF'], ['min' => 6, 'max' => 10], self::rising(0.6, 1), self::pulsing(12)),
            ]),

            'fathers_day' => self::theme('Fête des pères', $category, [
                self::layer(16, self::glyphs('mustache'), '#FFFFFF', ['min' => 12, 'max' => 20], self::falling(0.6, 1.2), self::swaying(20, 3), self::flipping(3, 5, 'horizontal')),
                self::layer(10, self::glyphs('trophy', 'medal', 'star'), '#FFD124', ['min' => 10, 'max' => 16], self::rising(0.5, 1), self::pulsing(8)),
            ]),

            'music_day' => self::theme('Fête de la musique', $category, [
                self::layer(30, self::glyphs('music', 'music-note'), ['#FFFFFF', '#FFD124', '#4AB0F5', '#F472B6'], ['min' => 10, 'max' => 18], self::rising(0.8, 1.6), self::swaying(30, 6), self::beat()),
                self::layer(4, self::glyphs('guitar', 'saxophone', 'microphone'), '#FFD124', ['min' => 18, 'max' => 24], self::wandering(0.6), self::spinning(2)),
            ]),

            'bastille_day' => self::theme('14 juillet', $category, [
                self::layer(30, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 2], self::still(), self::twinkling()),
            ], self::fireworks(['#2563EB', '#FFFFFF', '#EF4444'])),

            'back_to_school' => self::theme('Rentrée', $category, [
                self::layer(12, self::glyphs('paper-plane'), '#FFFFFF', ['min' => 12, 'max' => 18], self::drifting('right', 2, 3.5), self::swaying(40, 3)),
                self::layer(20, self::glyphs('pencil', 'book', 'ruler', 'backpack', 'apple-whole'), ['#FFD124', '#F87171', '#4AB0F5', '#A6D64D'], ['min' => 10, 'max' => 16], self::falling(0.6, 1.2), self::spinning(4)),
            ]),

            'halloween' => self::theme('Halloween', $category, [
                self::layer(14, self::circle(), '#FFFFFF', ['min' => 80, 'max' => 160], self::drifting('right', 0.2, 0.4), self::opacity(0.01, 0.025)),
                self::layer(14, self::glyphs('bat'), ['#C4B5FD', '#A78BFA'], ['min' => 10, 'max' => 18], self::wandering(4), self::swaying(40, 15)),
                self::layer(7, self::glyphs('ghost'), '#FFFFFF', ['min' => 16, 'max' => 24], self::rising(0.3, 0.6), self::swaying(30, 2), self::fading(0.6)),
                self::layer(5, self::glyphs('jack-o-lantern'), '#F97316', ['min' => 18, 'max' => 24], self::hopping(2)),
                self::layer(4, self::glyphs('spider'), '#E5E7EB', ['min' => 8, 'max' => 12], self::falling(0.2, 0.4), self::trembling()),
            ]),

            'st_nicolas' => self::theme('Saint-Nicolas', $category, [
                self::layer(80, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 4], self::falling(0.5, 1.2), self::swaying(15, 4), self::opacity(0.3, 0.8)),
                self::layer(10, self::glyphs('gingerbread-man'), '#D97706', ['min' => 14, 'max' => 20], self::hopping(1.5)),
                self::layer(12, self::glyphs('candy-cane', 'cookie', 'gift'), ['#EF4444', '#FFFFFF', '#FFD124'], ['min' => 10, 'max' => 16], self::falling(0.6, 1.2), self::spinning(4)),
            ]),

            'christmas_market' => self::theme('Marché de Noël', $category, [
                self::layer(40, self::circle(), ['#FFD124', '#FDE68A', '#F97316'], ['min' => 2, 'max' => 4], self::still(), self::twinkling()),
                self::layer(8, self::glyphs('mug-hot'), ['#F87171', '#FFFFFF'], ['min' => 14, 'max' => 20], self::drifting('right', 0.3, 0.6), self::swaying(10, 1)),
                self::layer(30, self::circle(), '#FFFFFF', ['min' => 10, 'max' => 24], self::rising(0.4, 0.8), self::swaying(20, 2), self::opacity(0.03, 0.08)),
                self::layer(60, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 3], self::falling(0.5, 1.2), self::swaying(15, 4), self::opacity(0.3, 0.8)),
            ]),

            'christmas' => self::theme('Noël', $category, [
                self::layer(120, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 5], self::falling(0.6, 1.6), self::swaying(20, 5), self::opacity(0.3, 0.9)),
                self::layer(12, self::glyphs('ornament'), ['#EF4444', '#FFD124', '#22C55E', '#F472B6'], ['min' => 12, 'max' => 20], self::falling(0.5, 1), self::swaying(15, 2), self::flipping(1, 3, 'horizontal')),
                self::layer(6, self::glyphs('tree-christmas', 'gift', 'bell'), ['#22C55E', '#EF4444', '#FFD124'], ['min' => 14, 'max' => 18], self::falling(0.5, 0.9), self::spinning(2)),
                self::layer(25, self::glyphs('sparkle'), '#FFD124', ['min' => 4, 'max' => 8], self::still(), self::twinkling()),
            ], [self::shootingStar('#FFD124')]),

            'diwali' => self::theme('Fête des lumières', $category, [
                self::layer(70, self::circle(), ['#FFD124', '#F97316', '#FDE68A'], ['min' => 2, 'max' => 5], self::rising(0.3, 0.8), self::swaying(10, 2), self::twinkling()),
                self::layer(8, self::glyphs('candle-holder', 'fire-flame-simple'), ['#FFD124', '#F97316'], ['min' => 12, 'max' => 18], self::wandering(0.3), self::trembling()),
                self::layer(30, self::glyphs('sparkle'), '#FDE68A', ['min' => 3, 'max' => 5], self::still(), self::twinkling()),
            ]),

            'neighbours_day' => self::theme('Fête des voisins', $category, [
                self::layer(8, self::glyphs('house-chimney'), ['#FDE68A', '#FFFFFF', '#FBCFE8'], ['min' => 14, 'max' => 20], self::drifting('right', 0.3, 0.6)),
                self::layer(6, self::glyphs('people-group', 'handshake'), '#FFFFFF', ['min' => 14, 'max' => 18], self::wandering(0.4), self::pulsing(3)),
                self::layer(70, self::square(), ['#A6D64D', '#4AB0F5', '#ED733D', '#FFD124', '#F472B6'], ['min' => 2, 'max' => 4], self::falling(0.8, 1.6), self::fluttering()),
            ]),

            'remembrance' => self::theme('Commémorations', $category, [
                self::layer(22, self::glyphs('flower'), ['#60A5FA', '#3B82F6', '#93C5FD'], ['min' => 10, 'max' => 16], self::falling(0.3, 0.7), self::swaying(15, 2), self::spinning(1)),
                self::layer(10, self::glyphs('flower'), ['#EF4444', '#DC2626'], ['min' => 10, 'max' => 14], self::falling(0.3, 0.6), self::swaying(15, 2), self::spinning(1)),
                self::layer(30, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 2], self::still(), self::twinkling()),
            ]),

            'europe_day' => self::theme('Journée de l\'Europe', $category, [
                // The dashboard background already plays the blue of the flag.
                self::layer(18, self::glyphs('star'), '#FFD124', ['min' => 9, 'max' => 14], self::wandering(0.4), self::spinning(2), self::opacity(0.45, 0.5)),
                self::layer(30, self::glyphs('star'), '#FFD124', ['min' => 3, 'max' => 5], self::still(), self::twinkling()),
                self::layer(70, self::square(), ['#FFD124', '#FFFFFF'], ['min' => 3, 'max' => 5], self::falling(0.6, 1.2), self::fluttering()),
            ]),

            'saint_jean' => self::theme('Feux de la Saint-Jean', $category, [
                self::layer(90, self::circle(), ['#F97316', '#FFD124', '#EF4444'], ['min' => 1, 'max' => 3], self::rising(1.5, 3), self::swaying(15, 8), self::fading(2)),
                self::layer(4, self::glyphs('campfire'), ['#F97316', '#FDBA74'], ['min' => 18, 'max' => 24], self::wandering(0.2), self::trembling()),
                self::layer(20, self::circle(), '#E5E7EB', ['min' => 30, 'max' => 60], self::rising(0.5, 1), self::swaying(40, 1), self::opacity(0.01, 0.025)),
            ]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function seasons(): array
    {
        $category = self::CATEGORY_SEASONS;

        return [
            'spring' => self::theme('Printemps', $category, [
                self::layer(18, self::glyphs('flower', 'flower-tulip', 'flower-daffodil'), ['#F9A8D4', '#FDE68A', '#C4B5FD', '#FFFFFF'], ['min' => 10, 'max' => 18], self::rising(0.3, 0.7), self::swaying(20, 2), self::spinning(2)),
                self::layer(8, self::glyphs('butterfly'), ['#FDE68A', '#F9A8D4', '#A5F3FC'], ['min' => 10, 'max' => 14], self::wandering(1.5), self::swaying(40, 10), self::pulsing(20)),
                self::layer(50, self::circle(), '#FEF9C3', ['min' => 1, 'max' => 2], self::wandering(0.4), self::twinkling()),
            ]),

            'summer' => self::theme('Été', $category, [
                self::layer(1, self::glyphs('sun'), '#FFD124', 60, self::still(), self::spinning(1)),
                self::layer(5, self::glyphs('volleyball'), ['#FFFFFF', '#FDE68A'], ['min' => 14, 'max' => 20], self::hopping(2), self::spinning(10)),
                self::layer(6, self::glyphs('umbrella-beach', 'tree-palm'), ['#FDE68A', '#86EFAC'], ['min' => 14, 'max' => 20], self::drifting('left', 0.3, 0.6)),
                self::layer(40, self::circle(), ['#FFFFFF', '#A5F3FC'], ['min' => 2, 'max' => 5], self::rising(0.5, 1), self::opacity(0.15, 0.4)),
            ]),

            'autumn' => self::theme('Automne', $category, [
                self::layer(34, self::glyphs('leaf-maple', 'leaf-oak', 'leaf'), ['#F97316', '#DC2626', '#F59E0B', '#B45309'], ['min' => 10, 'max' => 18], self::drifting('bottom-left', 0.6, 1.4), self::fluttering()),
                self::layer(6, self::glyphs('acorn', 'mushroom'), ['#D97706', '#FDE68A'], ['min' => 10, 'max' => 14], self::hopping(1)),
            ]),

            'winter' => self::theme('Hiver', $category, [
                self::layer(40, self::glyphs('snowflake'), ['#FFFFFF', '#BFDBFE', '#A5F3FC'], ['min' => 6, 'max' => 20], self::falling(0.4, 1.2), self::swaying(20, 3), self::spinning(3)),
                self::layer(60, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 2], self::still(), self::twinkling()),
            ]),

            'rain' => self::theme('Pluie', $category, [
                // Short strokes aligned on their direction.
                self::layer(160, ['type' => 'line'], ['#BFDBFE', '#FFFFFF'], ['min' => 6, 'max' => 12], self::drifting('bottom-left', 14, 20), self::opacity(0.3, 0.5), [
                    'move' => ['straight' => true],
                    'rotate' => ['path' => true],
                    'paint' => ['stroke' => ['width' => 1.5, 'color' => ['value' => ['#BFDBFE', '#FFFFFF']]]],
                ]),
                self::layer(4, self::glyphs('umbrella'), ['#FFD124', '#F472B6', '#4AB0F5'], ['min' => 18, 'max' => 24], self::wandering(0.4), self::swaying(15, 2)),
            ]),

            'heatwave' => self::theme('Canicule', $category, [
                self::layer(1, self::glyphs('sun-bright'), '#FFD124', 80, self::still(), self::spinning(1), self::pulsing(2)),
                self::layer(24, self::circle(), ['#F97316', '#FFD124'], ['min' => 30, 'max' => 70], self::rising(0.3, 0.6), self::swaying(40, 1), self::opacity(0.03, 0.08)),
                self::layer(10, self::glyphs('cube', 'glass-water'), ['#A5F3FC', '#FFFFFF'], ['min' => 10, 'max' => 14], self::falling(0.5, 1), self::spinning(4)),
            ]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function causes(): array
    {
        $category = self::CATEGORY_CAUSES;

        return [
            'womens_day' => self::theme('Journée internationale des droits des femmes', $category, [
                self::layer(18, self::glyphs('venus'), ['#C084FC', '#E879F9', '#FFFFFF'], ['min' => 10, 'max' => 18], self::rising(0.4, 0.9), self::swaying(15, 2), self::pulsing(4)),
                self::layer(60, self::circle(), '#FDE047', ['min' => 2, 'max' => 4], self::falling(0.5, 1.2), self::swaying(15, 4)),
            ]),

            'pink_october' => self::theme('Octobre rose', $category, [
                self::layer(25, self::circle(), ['#F9A8D4', '#F472B6', '#EC4899'], ['min' => 20, 'max' => 60], self::wandering(0.3), self::opacity(0.04, 0.12)),
                self::layer(22, self::glyphs('ribbon'), ['#F472B6', '#EC4899', '#FBCFE8'], ['min' => 12, 'max' => 22], self::rising(0.6, 1.3), self::swaying(15, 3), self::flipping(2, 5, 'horizontal')),
                self::layer(10, self::glyphs('heart'), '#FFFFFF', ['min' => 5, 'max' => 9], self::rising(0.4, 0.8), self::pulsing(10)),
            ]),

            'movember' => self::theme('Movember', $category, [
                self::layer(24, self::glyphs('mustache'), ['#FFFFFF', '#93C5FD'], ['min' => 10, 'max' => 20], self::falling(0.6, 1.4), self::swaying(25, 4), self::flipping(2, 5, 'horizontal')),
                self::layer(6, self::glyphs('mustache'), '#FFD124', ['min' => 18, 'max' => 24], self::hopping(2)),
            ]),

            'pride' => self::theme('Mois des fiertés', $category, [
                self::layer(120, self::square(), ['#EF4444', '#F97316', '#FFD124', '#22C55E', '#3B82F6', '#A855F7'], ['min' => 3, 'max' => 5], self::falling(0.8, 1.8), self::fluttering()),
                self::layer(4, self::glyphs('rainbow'), '#FFFFFF', ['min' => 22, 'max' => 30], self::wandering(0.4), self::swaying(20, 2)),
                self::layer(14, self::glyphs('heart'), ['#EF4444', '#F97316', '#FFD124', '#22C55E', '#3B82F6', '#A855F7'], ['min' => 8, 'max' => 14], self::rising(0.5, 1), self::pulsing(10)),
            ]),

            'earth_day' => self::theme('Jour de la Terre', $category, [
                self::layer(3, self::glyphs('earth-europe'), ['#4AB0F5', '#22C55E'], ['min' => 22, 'max' => 30], self::wandering(0.3), self::spinning(1)),
                self::layer(26, self::glyphs('leaf', 'seedling'), ['#22C55E', '#86EFAC', '#A6D64D'], ['min' => 8, 'max' => 14], self::rising(0.4, 0.9), self::fluttering()),
                self::layer(50, self::circle(), '#A5F3FC', ['min' => 1, 'max' => 2], self::still(), self::twinkling()),
            ]),

            'blood_donation' => self::theme('Don du sang', $category, [
                self::layer(30, self::glyphs('droplet'), ['#EF4444', '#DC2626'], ['min' => 6, 'max' => 12], self::dripping()),
                self::layer(10, self::glyphs('heart', 'hand-holding-heart'), ['#FFFFFF', '#FCA5A5'], ['min' => 12, 'max' => 18], self::rising(0.4, 0.8), self::pulsing(10)),
            ]),

            'no_tobacco' => self::theme('Mois sans tabac', $category, [
                self::layer(6, self::glyphs('lungs'), '#FCA5A5', ['min' => 18, 'max' => 26], self::wandering(0.3), self::pulsing(2)),
                self::layer(8, self::glyphs('ban-smoking'), '#FFFFFF', ['min' => 12, 'max' => 18], self::falling(0.4, 0.8), self::spinning(2)),
                self::layer(50, self::circle(), '#A5F3FC', ['min' => 2, 'max' => 5], self::rising(0.6, 1.2), self::swaying(10, 3)),
            ]),

            'health_week' => self::theme('Santé et sport', $category, [
                self::layer(8, self::glyphs('person-running', 'person-biking', 'person-swimming'), '#FFFFFF', ['min' => 14, 'max' => 20], self::drifting('right', 1, 2.5)),
                self::layer(6, self::glyphs('dumbbell'), '#A6D64D', ['min' => 14, 'max' => 18], self::hopping(1.5)),
                self::layer(8, self::glyphs('heart-pulse', 'apple-whole'), ['#F87171', '#A6D64D'], ['min' => 12, 'max' => 16], self::wandering(0.5), self::pulsing(8)),
            ]),

            'mobility_week' => self::theme('Semaine de la mobilité', $category, [
                self::layer(14, self::glyphs('bicycle', 'scooter', 'bus', 'person-running'), ['#FFFFFF', '#A6D64D', '#4AB0F5'], ['min' => 12, 'max' => 20], self::drifting('right', 0.8, 3)),
                self::layer(24, self::glyphs('leaf'), ['#22C55E', '#86EFAC'], ['min' => 6, 'max' => 10], self::drifting('bottom-right', 0.6, 1.2), self::fluttering()),
            ]),

            'disability_week' => self::theme('Semaine du handicap', $category, [
                self::layer(14, self::glyphs('universal-access', 'hand-holding-heart', 'handshake-angle'), ['#FFFFFF', '#4AB0F5', '#FFD124'], ['min' => 12, 'max' => 18], self::rising(0.4, 0.8), self::swaying(15, 2)),
                self::layer(60, self::square(), ['#A6D64D', '#4AB0F5', '#ED733D', '#FFD124'], ['min' => 2, 'max' => 4], self::falling(0.6, 1.4), self::fluttering()),
            ]),

            'zero_waste' => self::theme('Réduction des déchets', $category, [
                self::layer(8, self::glyphs('recycle'), '#A6D64D', ['min' => 16, 'max' => 24], self::wandering(0.3), self::spinning(3)),
                self::layer(30, self::glyphs('leaf'), ['#22C55E', '#86EFAC', '#A6D64D'], ['min' => 8, 'max' => 12], self::drifting('bottom-right', 0.6, 1.2), self::fluttering()),
            ]),

            'veggie' => self::theme('Journée végétarienne', $category, [
                self::layer(20, self::glyphs('seedling'), ['#22C55E', '#86EFAC'], ['min' => 6, 'max' => 18], self::rising(0.4, 0.8), self::pulsing(2)),
                self::layer(14, self::glyphs('carrot', 'salad', 'avocado', 'tomato', 'corn'), ['#F97316', '#A6D64D', '#EF4444', '#FFD124'], ['min' => 12, 'max' => 16], self::falling(0.5, 1), self::spinning(3)),
            ]),

            'telethon' => self::theme('Téléthon', $category, [
                self::layer(26, self::glyphs('heart'), ['#EF4444', '#3B82F6', '#FFFFFF'], ['min' => 8, 'max' => 16], self::rising(0.5, 1.1), self::swaying(15, 3), self::pulsing(10)),
                self::layer(6, self::glyphs('hand-holding-heart'), '#FFFFFF', ['min' => 16, 'max' => 20], self::wandering(0.3)),
                self::layer(40, self::glyphs('sparkle'), '#FFD124', ['min' => 3, 'max' => 5], self::still(), self::twinkling()),
            ]),

            'orange_day' => self::theme('Journée internationale pour l\'élimination de la violence à l\'égard des femmes', $category, [
                self::layer(22, self::glyphs('ribbon'), ['#F97316', '#FB923C', '#EA580C'], ['min' => 12, 'max' => 20], self::rising(0.4, 0.9), self::swaying(15, 2), self::flipping(2, 4, 'horizontal')),
                self::layer(20, self::circle(), ['#F97316', '#FDBA74'], ['min' => 20, 'max' => 50], self::wandering(0.3), self::opacity(0.04, 0.1)),
            ]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function sport(): array
    {
        $category = self::CATEGORY_SPORT;

        return [
            'tour_de_france' => self::theme('Tour de France', $category, [
                self::layer(10, self::glyphs('bicycle'), ['#FFD124', '#22C55E', '#FFFFFF'], ['min' => 14, 'max' => 22], self::drifting('right', 2, 5)),
                self::layer(6, self::glyphs('shirt'), ['#FFD124', '#22C55E', '#FFFFFF'], ['min' => 10, 'max' => 14], self::falling(0.6, 1), self::flipping(3, 6, 'horizontal')),
                self::layer(4, self::glyphs('flag-checkered'), '#FFFFFF', ['min' => 14, 'max' => 18], self::wandering(0.6), self::swaying(20, 4)),
            ]),

            'world_cup' => self::theme('Coupe du monde / Euro', $category, [
                self::layer(8, self::glyphs('futbol'), '#FFFFFF', ['min' => 14, 'max' => 20], self::hopping(2.5), self::spinning(10)),
                self::layer(90, self::square(), ['#2563EB', '#FFFFFF', '#EF4444'], ['min' => 3, 'max' => 5], self::falling(0.8, 1.8), self::fluttering()),
                self::layer(2, self::glyphs('trophy'), '#FFD124', ['min' => 24, 'max' => 30], self::wandering(0.3), self::pulsing(3)),
            ]),

            'olympics' => self::theme('Jeux olympiques', $category, [
                self::layer(15, self::circle(), ['#3B82F6', '#FFD124', '#E5E7EB', '#22C55E', '#EF4444'], ['min' => 12, 'max' => 18], self::drifting('right', 0.4, 0.8), self::swaying(20, 2), self::ring(4)),
                self::layer(14, self::glyphs('medal'), ['#FFD124', '#E5E7EB', '#D97706'], ['min' => 12, 'max' => 18], self::falling(0.6, 1.2), self::flipping(3, 6, 'horizontal')),
                self::layer(3, self::glyphs('fire-flame-simple'), '#F97316', ['min' => 16, 'max' => 20], self::rising(0.4, 0.8), self::trembling()),
            ]),

            'rugby' => self::theme('Rugby', $category, [
                self::layer(8, self::glyphs('football'), ['#FDBA74', '#FFFFFF'], ['min' => 14, 'max' => 20], self::hopping(2.5), self::spinning(20)),
                self::layer(60, self::square(), ['#2563EB', '#FFFFFF', '#EF4444'], ['min' => 3, 'max' => 5], self::falling(0.8, 1.6), self::fluttering()),
                self::layer(40, self::circle(), '#22C55E', ['min' => 1, 'max' => 2], self::rising(1, 2), self::opacity(0.4, 0.7)),
            ]),

            'tennis' => self::theme('Roland-Garros', $category, [
                self::layer(8, self::glyphs('tennis-ball'), '#D9F99D', ['min' => 10, 'max' => 14], self::pinball()),
                self::layer(3, self::glyphs('racquet'), '#FFFFFF', ['min' => 20, 'max' => 26], self::wandering(0.4), self::spinning(3)),
                self::layer(70, self::circle(), ['#EA580C', '#C2410C'], ['min' => 1, 'max' => 3], self::drifting('bottom-right', 0.4, 1), self::opacity(0.4, 0.8)),
            ]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function food(): array
    {
        $category = self::CATEGORY_FOOD;
        $crepe = self::layer(0, self::circle(), ['#E9B872', '#F2C98A', '#D99A4E'], ['min' => 14, 'max' => 22], self::flipping(15, 25), ['roll' => ['darken' => ['value' => 15]]]);
        $sandwich = self::layer(0, self::glyphs('sandwich'), ['#F3D18B', '#E9B872'], ['min' => 16, 'max' => 22], self::spinning(6));
        $popcorn = self::layer(0, self::circle(), ['#FFFBEB', '#FDE68A'], ['min' => 2, 'max' => 5]);
        $foodTruck = self::layer(0, self::glyphs('hotdog', 'taco', 'burger', 'burrito', 'french-fries'), ['#FDBA74', '#FFD124', '#F87171'], ['min' => 14, 'max' => 20], self::flipping(6, 12, 'horizontal'));

        return [
            'crepes' => self::theme('Crêpes (Chandeleur)', $category, [
                self::layer(70, self::circle(), '#FFFFFF', ['min' => 0.5, 'max' => 1.5], self::falling(0.3, 0.8), self::opacity(0.3, 0.7)),
                self::layer(10, self::glyphs('strawberry', 'lemon', 'banana'), ['#F87171', '#FFD124'], ['min' => 8, 'max' => 12], self::falling(0.6, 1), self::spinning(3)),
            ], [self::toss($crepe, 20, 1.8), self::toss($crepe, 50, 2.3), self::toss($crepe, 80, 2)]),

            'waffles' => self::theme('Gaufres', $category, [
                self::layer(150, self::circle(), '#FFFFFF', ['min' => 0.5, 'max' => 2], self::falling(0.4, 1), self::swaying(10, 3), self::opacity(0.4, 0.9)),
                self::layer(14, self::glyphs('waffle'), ['#E9B872', '#D99A4E'], ['min' => 14, 'max' => 22], self::falling(0.4, 0.8), self::flipping(1, 3)),
            ]),

            'french_toast' => self::theme('Pain perdu', $category, [
                self::layer(30, self::glyphs('droplet'), ['#F59E0B', '#D97706'], ['min' => 5, 'max' => 9], self::dripping()),
                self::layer(12, self::glyphs('bread-slice'), ['#E9B872', '#F2C98A'], ['min' => 14, 'max' => 20], self::rising(0.3, 0.6), self::swaying(20, 2), self::spinning(2)),
                self::layer(6, self::glyphs('egg', 'butter'), ['#FFFBEB', '#FDE68A'], ['min' => 10, 'max' => 14], self::wandering(0.5)),
            ]),

            'croque_monsieur' => self::theme('Croque-monsieur', $category, [
                self::layer(16, self::glyphs('cheese-swiss'), '#FACC15', ['min' => 10, 'max' => 16], self::falling(0.2, 0.5), self::pulsing(3)),
            ], [self::toss($sandwich, 30, 3, 2), self::toss($sandwich, 70, 3.5, 2)]),

            'ice_cream' => self::theme('Glaces', $category, [
                self::layer(14, self::glyphs('ice-cream'), ['#FBCFE8', '#FEF3C7', '#BBF7D0', '#C4B5FD'], ['min' => 14, 'max' => 22], self::falling(0.5, 1), self::swaying(15, 3)),
                self::layer(40, self::circle(), ['#FBCFE8', '#FEF3C7', '#BBF7D0', '#C4B5FD', '#A5F3FC'], ['min' => 3, 'max' => 9], self::dripping(), self::pulsing(4)),
            ]),

            'granita' => self::theme('Granités', $category, [
                self::layer(80, self::square(), ['#22D3EE', '#F43F5E', '#A3E635', '#E879F9'], ['min' => 2, 'max' => 5], self::falling(0.3, 0.8), self::spinning(8), self::trembling(), self::opacity(0.5, 0.9)),
                self::layer(10, self::glyphs('cup-straw'), ['#22D3EE', '#F43F5E', '#A3E635'], ['min' => 14, 'max' => 20], self::rising(0.4, 0.8), self::swaying(15, 2)),
            ]),

            'donuts' => self::theme('Beignets', $category, [
                self::layer(14, self::circle(), ['#F9A8D4', '#FDE68A', '#D97706', '#C4B5FD'], ['min' => 12, 'max' => 18], self::hopping(2), self::ring(7)),
                self::layer(60, self::square(), ['#F472B6', '#FFD124', '#4AB0F5', '#A6D64D'], ['min' => 1, 'max' => 2], self::falling(0.6, 1.2), self::spinning(10)),
            ]),

            'fun_fair' => self::theme('Fête foraine', $category, [
                self::layer(10, self::circle(), ['#F9A8D4', '#C4B5FD'], ['min' => 30, 'max' => 50], self::wandering(0.4), self::opacity(0.15, 0.3)),
                self::layer(10, self::glyphs('ticket', 'candy', 'popcorn'), ['#FFD124', '#F472B6'], ['min' => 10, 'max' => 16], self::falling(0.6, 1.2), self::flipping(4, 8, 'horizontal')),
            ], [self::pop($popcorn, 30), self::pop($popcorn, 70)]),

            'cookies' => self::theme('Cookies', $category, [
                self::layer(8, self::glyphs('cookie'), '#D99A4E', ['min' => 16, 'max' => 22], self::hopping(2), self::spinning(8)),
                self::layer(50, self::circle(), ['#78350F', '#92400E'], ['min' => 2, 'max' => 4], self::falling(0.6, 1.4), self::opacity(0.7, 1)),
            ]),

            'bubble_tea' => self::theme('Bubble tea', $category, [
                self::layer(60, self::circle(), ['#4A2C1A', '#6B4226'], ['min' => 4, 'max' => 8], self::rising(0.6, 1.4), self::swaying(10, 4), self::outlined('#FDE68A', 1)),
                self::layer(8, self::glyphs('cup-straw'), ['#FDE68A', '#FBCFE8', '#BBF7D0'], ['min' => 16, 'max' => 22], self::wandering(0.4), self::swaying(15, 2)),
            ]),

            'hot_chocolate' => self::theme('Chocolat chaud', $category, [
                self::layer(8, self::glyphs('mug-hot'), ['#D99A4E', '#FFFFFF'], ['min' => 16, 'max' => 22], self::wandering(0.3)),
                self::layer(30, self::circle(), '#FFFFFF', ['min' => 12, 'max' => 30], self::rising(0.5, 1), self::swaying(25, 2), self::opacity(0.03, 0.08), self::pulsing(3)),
                self::layer(20, self::square(), '#FFFFFF', ['min' => 4, 'max' => 7], self::falling(0.4, 0.8), self::spinning(5)),
            ]),

            'wraps' => self::theme('Wraps', $category, [
                self::layer(10, self::glyphs('burrito'), ['#F2C98A', '#E9B872'], ['min' => 14, 'max' => 20], self::drifting('right', 0.8, 1.6), self::spinning(6)),
                self::layer(30, self::glyphs('leaf'), ['#22C55E', '#86EFAC'], ['min' => 6, 'max' => 10], self::falling(0.6, 1.2), self::fluttering()),
                self::layer(20, self::circle(), '#EF4444', ['min' => 3, 'max' => 5], self::falling(0.8, 1.4)),
            ]),

            'brunch' => self::theme('Brunch', $category, [
                self::layer(20, self::glyphs('croissant', 'egg-fried', 'bacon', 'pancakes', 'mug-saucer'), ['#F2C98A', '#FFFFFF', '#FDE68A'], ['min' => 12, 'max' => 18], self::wandering(0.6), self::spinning(2)),
                self::layer(40, self::circle(), '#FDE68A', ['min' => 1, 'max' => 2], self::still(), self::twinkling()),
            ]),

            'street_food' => self::theme('Street food', $category, [
                self::layer(40, self::circle(), ['#FFD124', '#EF4444'], ['min' => 2, 'max' => 4], self::dripping()),
            ], [self::toss($foodTruck, 25, 1.6), self::toss($foodTruck, 75, 1.9)]),

            'burger' => self::theme('Burger', $category, [
                self::layer(10, self::glyphs('burger'), '#ED733D', ['min' => 16, 'max' => 22], self::pinball()),
                self::layer(10, self::glyphs('french-fries'), '#FFD124', ['min' => 14, 'max' => 20], self::pinball()),
                self::layer(5, self::glyphs('cup-straw'), '#4AB0F5', ['min' => 14, 'max' => 18], self::pinball()),
            ]),

            'hot_dog' => self::theme('Hot-dog', $category, [
                self::layer(12, self::glyphs('hotdog'), '#FDBA74', ['min' => 16, 'max' => 22], self::drifting('right', 1.5, 3), self::swaying(20, 3)),
                self::layer(50, self::glyphs('droplet'), ['#FFD124', '#EF4444'], ['min' => 4, 'max' => 7], self::dripping()),
            ]),

            'bbq' => self::theme('Barbecue', $category, [
                self::layer(60, self::circle(), ['#F97316', '#FFD124', '#EF4444'], ['min' => 1, 'max' => 3], self::rising(1.5, 3), self::swaying(15, 8), self::fading(2)),
                self::layer(20, self::circle(), '#E5E7EB', ['min' => 30, 'max' => 60], self::rising(0.5, 1), self::swaying(40, 1), self::opacity(0.02, 0.06)),
                self::layer(10, self::glyphs('meat', 'drumstick-bite', 'sausage', 'steak', 'corn'), ['#FDBA74', '#F87171', '#FFD124'], ['min' => 14, 'max' => 20], self::wandering(0.4), self::trembling()),
            ]),

            'soups' => self::theme('Soupes et pot-au-feu', $category, [
                self::layer(8, self::glyphs('bowl-hot'), ['#FDBA74', '#FFFFFF'], ['min' => 16, 'max' => 22], self::wandering(0.3)),
                self::layer(30, self::circle(), '#FFFFFF', ['min' => 12, 'max' => 30], self::rising(0.5, 1), self::swaying(25, 2), self::opacity(0.03, 0.08)),
                self::layer(16, self::glyphs('carrot', 'onion', 'garlic', 'potato'), ['#F97316', '#FDE68A', '#FFFFFF'], ['min' => 10, 'max' => 14], self::falling(0.6, 1.2), self::spinning(4)),
            ]),

            'healthy' => self::theme('Healthy / Détox', $category, [
                self::layer(26, self::glyphs('leaf', 'seedling'), ['#22C55E', '#86EFAC', '#A6D64D'], ['min' => 8, 'max' => 16], self::rising(0.4, 0.9), self::swaying(20, 3), self::fluttering()),
                self::layer(12, self::glyphs('avocado', 'carrot', 'apple-whole', 'salad', 'lemon'), ['#A6D64D', '#F97316', '#F87171', '#FFD124'], ['min' => 12, 'max' => 16], self::wandering(0.6), self::spinning(2)),
            ]),

            'seafood' => self::theme('Poisson et fruits de mer', $category, [
                self::layer(12, self::glyphs('fish', 'shrimp'), ['#FDBA74', '#FFFFFF', '#A5F3FC'], ['min' => 12, 'max' => 20], self::drifting('right', 0.8, 1.8), self::swaying(25, 3)),
                self::layer(50, self::circle(), '#FFFFFF', ['min' => 2, 'max' => 6], self::rising(0.8, 1.6), self::swaying(10, 5), self::opacity(0.15, 0.35)),
            ]),

            'picnic' => self::theme('Pique-nique', $category, [
                self::layer(12, self::glyphs('sandwich', 'apple-whole', 'watermelon-slice', 'cherries'), ['#F2C98A', '#F87171', '#A6D64D'], ['min' => 12, 'max' => 18], self::wandering(0.4), self::spinning(2)),
                self::layer(30, self::glyphs('leaf'), ['#22C55E', '#86EFAC'], ['min' => 6, 'max' => 10], self::drifting('bottom-right', 0.6, 1.2), self::fluttering()),
                self::layer(4, self::glyphs('sun'), '#FFD124', ['min' => 8, 'max' => 12], self::still(), self::spinning(1), self::pulsing(2)),
            ]),

            'fruits' => self::theme('Fruits', $category, [
                self::layer(26, self::glyphs('strawberry', 'cherries', 'banana', 'pear', 'peach', 'pineapple', 'watermelon-slice', 'blueberries'), ['#F87171', '#FFD124', '#A6D64D', '#FDBA74', '#C4B5FD'], ['min' => 12, 'max' => 18], self::pinball()),
            ]),

            'coffee' => self::theme('Café', $category, [
                self::layer(30, self::glyphs('coffee-bean'), ['#B45309', '#D97706'], ['min' => 6, 'max' => 10], self::falling(0.6, 1.2), self::spinning(6)),
                self::layer(6, self::glyphs('mug-hot'), ['#FFFFFF', '#FDE68A'], ['min' => 16, 'max' => 22], self::wandering(0.3)),
                self::layer(24, self::circle(), '#FFFFFF', ['min' => 12, 'max' => 28], self::rising(0.5, 1), self::swaying(25, 2), self::opacity(0.03, 0.08)),
            ]),

            'bread' => self::theme('Fête du pain', $category, [
                self::layer(120, self::circle(), '#FFFFFF', ['min' => 0.5, 'max' => 2], self::falling(0.3, 0.8), self::swaying(10, 3), self::opacity(0.4, 0.8)),
                self::layer(12, self::glyphs('baguette', 'croissant', 'bread-slice'), ['#E9B872', '#F2C98A'], ['min' => 14, 'max' => 20], self::falling(0.4, 0.8), self::spinning(3)),
                self::layer(14, self::glyphs('wheat'), '#FDE68A', ['min' => 12, 'max' => 18], self::rising(0.3, 0.6), self::swaying(20, 2)),
            ]),

            'gastronomy' => self::theme('Fête de la gastronomie', $category, [
                self::layer(8, self::glyphs('hat-chef'), '#FFFFFF', ['min' => 14, 'max' => 20], self::wandering(0.4), self::swaying(15, 2)),
                self::layer(14, self::glyphs('utensils'), ['#E5E7EB', '#FFD124'], ['min' => 10, 'max' => 16], self::falling(0.5, 1), self::spinning(4)),
                self::layer(50, self::glyphs('sparkle'), '#FFD124', ['min' => 3, 'max' => 5], self::still(), self::twinkling()),
            ]),

            'taste_week' => self::theme('Semaine du goût', $category, [
                self::layer(40, self::glyphs('carrot', 'apple-whole', 'cheese', 'lemon', 'pepper-hot', 'wheat', 'grapes', 'strawberry'), ['#F97316', '#F87171', '#FFD124', '#A6D64D', '#C4B5FD'], ['min' => 10, 'max' => 16], self::swirling()),
            ]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function regions(): array
    {
        $category = self::CATEGORY_REGIONS;

        return [
            'nord' => self::theme('Spécialités du Nord', $category, [
                self::layer(40, self::glyphs('droplet'), ['#FACC15', '#F59E0B'], ['min' => 5, 'max' => 9], self::dripping()),
                self::layer(10, self::glyphs('bread-slice', 'cheese'), ['#E9B872', '#FACC15'], ['min' => 14, 'max' => 20], self::rising(0.3, 0.6), self::spinning(2)),
                self::layer(8, self::glyphs('french-fries', 'beer-mug'), ['#FFD124', '#FDE68A'], ['min' => 12, 'max' => 18], self::hopping(1.5)),
            ]),

            'braderie' => self::theme('Braderie', $category, [
                self::layer(18, self::glyphs('tag'), ['#FFD124', '#ED733D', '#FFFFFF'], ['min' => 10, 'max' => 16], self::falling(0.8, 1.6), self::swaying(20, 4), self::flipping(4, 8, 'horizontal')),
                self::layer(12, self::glyphs('french-fries'), '#FFD124', ['min' => 12, 'max' => 18], self::hopping(1.5)),
            ]),

            'mountain' => self::theme('Montagne (raclette, fondue, tartiflette)', $category, [
                self::layer(160, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 4], self::drifting('bottom-right', 3, 6), self::opacity(0.3, 0.9)),
                self::layer(10, self::glyphs('cheese', 'potato', 'fondue-pot'), ['#FACC15', '#E9B872'], ['min' => 14, 'max' => 20], self::drifting('bottom-right', 1.5, 3), self::spinning(10)),
                self::layer(3, self::glyphs('mountain'), '#FFFFFF', ['min' => 30, 'max' => 40], self::drifting('left', 0.1, 0.2), self::opacity(0.08, 0.15)),
            ]),

            'brittany' => self::trip('Bretagne', ['#FFFFFF', '#9CA3AF'], ['sailboat', 'anchor', 'lighthouse'], $category),
            'alsace' => self::trip('Alsace', ['#EF4444', '#FFFFFF'], ['pretzel', 'sausage', 'bird'], $category),
            'south_west' => self::trip('Sud-Ouest', ['#EF4444', '#FFD124'], ['duck', 'grapes', 'sun'], $category),
            'provence' => self::trip('Provence', ['#A78BFA', '#C4B5FD', '#FFD124'], ['flower', 'olive', 'sun'], $category),
            'corsica' => self::trip('Corse', ['#FFFFFF', '#9CA3AF'], ['mountain', 'cheese', 'sun'], $category),
            'overseas' => self::trip('Outre-mer', ['#22C55E', '#FFD124', '#EF4444'], ['tree-palm', 'coconut', 'pineapple', 'mango', 'volcano'], $category),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function travel(): array
    {
        $category = self::CATEGORY_TRAVEL;
        $pizza = self::layer(0, self::glyphs('pizza'), ['#FDBA74', '#FCD34D'], ['min' => 18, 'max' => 26], self::spinning(40));

        return [
            'italian' => self::theme('À l\'italienne', $category, [
                self::layer(30, self::glyphs('leaf'), ['#22C55E', '#16A34A'], ['min' => 6, 'max' => 10], self::falling(0.6, 1.2), self::fluttering()),
                self::layer(20, self::circle(), ['#EF4444', '#FFFFFF'], ['min' => 3, 'max' => 6], self::falling(0.8, 1.4)),
            ], [self::toss($pizza, 35, 2.5), self::toss($pizza, 65, 3)]),

            'mexican' => self::theme('Mexique', $category, [
                self::layer(70, self::circle(), ['#F97316', '#FFD124', '#EF4444'], ['min' => 1, 'max' => 3], self::rising(1.5, 3), self::swaying(10, 8), self::fading(2)),
                self::layer(14, self::glyphs('pepper-hot'), ['#EF4444', '#F97316', '#A6D64D'], ['min' => 12, 'max' => 18], self::rising(0.8, 1.6), self::swaying(15, 5), self::trembling()),
                self::layer(8, self::glyphs('taco', 'avocado'), ['#FDE68A', '#A6D64D'], ['min' => 14, 'max' => 18], self::hopping(2)),
            ]),

            'asian' => self::theme('Asie', $category, [
                self::layer(70, self::circle(), ['#FBCFE8', '#F9A8D4', '#FFFFFF'], ['min' => 3, 'max' => 6], self::drifting('bottom-right', 0.8, 1.6), self::fluttering()),
                self::layer(10, self::circle(), ['#EF4444', '#F97316'], ['min' => 10, 'max' => 16], self::rising(0.3, 0.6), self::swaying(10, 1), self::fading(0.6, 0.5)),
                self::layer(8, self::glyphs('bowl-chopsticks-noodles', 'sushi', 'bowl-rice'), ['#FFFFFF', '#FDE68A'], ['min' => 14, 'max' => 18], self::wandering(0.4), self::spinning(2)),
            ]),

            'couscous' => self::theme('Couscous', $category, [
                self::layer(120, self::circle(), ['#F59E0B', '#EF4444', '#FFD124', '#FDE68A'], ['min' => 1, 'max' => 3], self::swirling()),
                self::layer(10, self::glyphs('pepper-hot', 'lemon', 'olive', 'carrot'), ['#EF4444', '#FFD124', '#A6D64D', '#F97316'], ['min' => 12, 'max' => 16], self::falling(0.5, 1), self::spinning(3)),
            ]),

            'france' => self::trip('France', ['#2563EB', '#FFFFFF', '#EF4444'], ['baguette', 'croissant', 'cheese']),
            'spain' => self::trip('Espagne', ['#EF4444', '#FFD124'], ['guitar', 'olive', 'sun']),
            'portugal' => self::trip('Portugal', ['#16A34A', '#EF4444', '#FFD124'], ['fish', 'anchor', 'sun']),
            'germany' => self::trip('Allemagne', ['#EF4444', '#FFD124', '#FFFFFF'], ['pretzel', 'sausage', 'beer-mug']),
            'belgium' => self::trip('Belgique', ['#EF4444', '#FFD124', '#FFFFFF'], ['french-fries', 'waffle', 'beer-mug']),
            'united_kingdom' => self::trip('Royaume-Uni', ['#2563EB', '#FFFFFF', '#EF4444'], ['mug-saucer', 'fish', 'crown']),
            'poland' => self::trip('Pologne', ['#FFFFFF', '#EF4444'], ['bowl-food', 'sausage', 'wheat']),
            'greece' => self::trip('Grèce', ['#2563EB', '#FFFFFF'], ['olive', 'sun', 'anchor']),
            'india' => self::trip('Inde', ['#F97316', '#FFFFFF', '#16A34A'], ['pepper-hot', 'bowl-rice', 'garlic']),
            'morocco' => self::trip('Maroc', ['#EF4444', '#16A34A'], ['lemon', 'olive', 'mug-saucer']),
            'lebanon' => self::trip('Liban', ['#EF4444', '#FFFFFF', '#16A34A'], ['tree', 'lemon', 'olive']),
            'usa' => self::trip('États-Unis', ['#2563EB', '#FFFFFF', '#EF4444'], ['burger', 'hotdog', 'star']),
            'louisiana' => self::trip('Louisiane', ['#A855F7', '#22C55E', '#FFD124'], ['saxophone', 'shrimp', 'music']),
            'brazil' => self::trip('Brésil', ['#16A34A', '#FFD124', '#2563EB'], ['futbol', 'tree-palm', 'pineapple']),
            'japan' => self::trip('Japon', ['#FFFFFF', '#EF4444'], ['sushi', 'bowl-chopsticks-noodles', 'chopsticks']),
            'thailand' => self::trip('Thaïlande', ['#EF4444', '#FFFFFF', '#2563EB'], ['elephant', 'bowl-chopsticks-noodles', 'pepper-hot']),
            'vietnam' => self::trip('Vietnam', ['#EF4444', '#FFD124'], ['bowl-hot', 'chopsticks', 'star']),
            'korea' => self::trip('Corée', ['#FFFFFF', '#EF4444', '#2563EB'], ['bowl-rice', 'pepper-hot', 'chopsticks']),
            'turkey' => self::trip('Turquie', ['#EF4444', '#FFFFFF'], ['meat', 'olive', 'mug-saucer']),
            'west_africa' => self::trip('Afrique de l\'Ouest', ['#16A34A', '#FFD124', '#EF4444'], ['drum', 'bowl-rice', 'sun']),
            'peru' => self::trip('Pérou', ['#EF4444', '#FFFFFF'], ['fish', 'lemon', 'mountain-sun']),
            'argentina' => self::trip('Argentine', ['#93C5FD', '#FFFFFF', '#FFD124'], ['steak', 'futbol', 'sun']),
            'hawaii' => self::trip('Hawaï', ['#22D3EE', '#F472B6', '#FFD124'], ['tree-palm', 'fish', 'pineapple']),
            'scandinavia' => self::trip('Scandinavie', ['#2563EB', '#FFD124', '#EF4444', '#FFFFFF'], ['fish', 'snowflake', 'mug-hot']),

            'canada' => self::theme('Canada', self::CATEGORY_TRAVEL, [
                self::layer(30, self::glyphs('leaf-maple'), ['#EF4444', '#DC2626', '#F97316'], ['min' => 10, 'max' => 20], self::drifting('bottom-right', 0.6, 1.4), self::fluttering()),
                self::layer(60, self::circle(), '#FFFFFF', ['min' => 1, 'max' => 3], self::falling(0.6, 1.2), self::opacity(0.3, 0.8)),
            ]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function company(): array
    {
        $category = self::CATEGORY_COMPANY;

        return [
            'ai' => self::neuralNetwork(),

            'hackathon' => self::theme('Hackathon', $category, [
                self::layer(90, self::characters(['0', '1'], 'monospace'), ['#A6D64D', '#86EFAC'], ['min' => 5, 'max' => 9], self::falling(2, 4), ['move' => ['straight' => true]]),
                self::layer(6, self::glyphs('code', 'terminal', 'brackets-curly'), '#A6D64D', ['min' => 16, 'max' => 22], self::wandering(0.4), self::pulsing(4)),
            ]),

            'anniversary' => self::theme('Anniversaire', $category, [
                self::layer(16, self::glyphs('balloon'), ['#F472B6', '#FFD124', '#4AB0F5', '#A6D64D', '#ED733D'], ['min' => 16, 'max' => 26], self::rising(0.6, 1.2), self::swaying(20, 2)),
                self::layer(4, self::glyphs('cake-candles'), '#FFFFFF', ['min' => 18, 'max' => 24], self::wandering(0.3), self::pulsing(3)),
            ], [self::pop(self::layer(0, self::square(), ['#A6D64D', '#4AB0F5', '#ED733D', '#FFD124', '#F472B6'], ['min' => 3, 'max' => 5], self::fluttering()), 50, 0.3)]),

            'housewarming' => self::theme('Crémaillère', $category, [
                self::layer(10, self::glyphs('house-heart'), ['#FFFFFF', '#FDE68A'], ['min' => 14, 'max' => 20], self::rising(0.4, 0.8), self::swaying(15, 2)),
                self::layer(14, self::glyphs('key'), '#FFD124', ['min' => 10, 'max' => 14], self::falling(0.6, 1.2), self::flipping(4, 8, 'horizontal')),
                self::layer(40, self::circle(), '#FDE68A', ['min' => 1, 'max' => 2], self::still(), self::twinkling()),
            ]),

            'cyber_month' => self::theme('Cybermois, mois européen de la cybersécurité', $category, [
                self::layer(70, self::characters(['0', '1'], 'monospace'), ['#4AB0F5', '#93C5FD'], ['min' => 5, 'max' => 8], self::falling(1.5, 3), ['move' => ['straight' => true]]),
                self::layer(10, self::glyphs('lock', 'shield-halved', 'key'), ['#FFFFFF', '#4AB0F5'], ['min' => 14, 'max' => 20], self::wandering(0.4), self::pulsing(3)),
                self::layer(4, self::glyphs('bug'), '#F87171', ['min' => 10, 'max' => 14], self::wandering(1), self::trembling()),
            ]),

            'seminar' => self::theme('Séminaire', $category, [
                self::layer(10, self::glyphs('lightbulb'), '#FFD124', ['min' => 12, 'max' => 18], self::wandering(0.4), self::pulsing(4)),
                self::layer(4, self::glyphs('presentation-screen', 'people-group'), '#FFFFFF', ['min' => 16, 'max' => 22], self::wandering(0.3)),
                self::layer(60, self::circle(), '#4AB0F5', ['min' => 1, 'max' => 3], self::wandering(0.6), ['links' => ['enable' => true, 'distance' => 120, 'opacity' => 0.25, 'color' => '#4AB0F5']]),
            ]),

            'annual_results' => self::theme('Résultats annuels', $category, [
                self::layer(8, self::glyphs('chart-line-up'), ['#A6D64D', '#FFFFFF'], ['min' => 14, 'max' => 20], self::rising(0.5, 1)),
                self::layer(10, self::glyphs('coins'), '#FFD124', ['min' => 12, 'max' => 16], self::hopping(1.5)),
                self::layer(50, self::glyphs('sparkle'), '#FFD124', ['min' => 3, 'max' => 5], self::still(), self::twinkling()),
            ]),
        ];
    }

    /**
     * Confetti in the flag colors, paper planes crossing the screen and a few local icons.
     *
     * @param  list<string>  $flagColors
     * @param  list<string>  $icons
     * @return array<string, mixed>
     */
    private static function trip(string $name, array $flagColors, array $icons, string $category = self::CATEGORY_TRAVEL): array
    {
        return self::theme($name, $category, [
            self::layer(80, self::square(), $flagColors, ['min' => 3, 'max' => 6], self::falling(0.8, 1.8), self::fluttering()),
            self::layer(5, self::glyphs('paper-plane'), '#FFFFFF', ['min' => 12, 'max' => 16], self::drifting('right', 2, 3), self::swaying(40, 2)),
            self::layer(12, self::glyphs(...$icons), $flagColors, ['min' => 12, 'max' => 18], self::wandering(0.5), self::spinning(2)),
        ]);
    }

    /**
     * Neurons linked together, with signals firing along the links.
     *
     * @return array<string, mixed>
     */
    private static function neuralNetwork(): array
    {
        return [
            'name' => 'Intelligence artificielle',
            'category' => self::CATEGORY_COMPANY,
            'hdr' => false,
            'particles' => [
                'number' => [
                    'value' => 120,
                    'density' => ['enable' => true],
                ],
                'paint' => [
                    'color' => ['value' => ['#4AB0F5', '#147DE8', '#A6D64D', '#FFFFFF']],
                    'fill' => ['opacity' => ['min' => 0.4, 'max' => 0.9]],
                ],
                'links' => [
                    'color' => '#4AB0F5',
                    'distance' => 160,
                    'enable' => true,
                    'opacity' => 0.4,
                    'width' => 1,
                    'triangles' => [
                        'enable' => true,
                        'color' => '#147DE8',
                        'opacity' => 0.03,
                    ],
                ],
                'twinkle' => [
                    'particles' => [
                        'enable' => true,
                        'frequency' => 0.05,
                        'opacity' => 1,
                        'fillColor' => '#A6D64D',
                    ],
                    'links' => [
                        'enable' => true,
                        'frequency' => 0.01,
                        'opacity' => 1,
                        'color' => '#A6D64D',
                    ],
                ],
                'move' => ['enable' => true, 'speed' => 1],
                'size' => ['value' => ['min' => 1, 'max' => 4]],
                'shape' => ['type' => 'circle'],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $layers
     * @param  list<array<string, mixed>>  $emitters
     * @return array<string, mixed>
     */
    private static function theme(string $name, string $category, array $layers, array $emitters = []): array
    {
        $groups = [];
        foreach ($layers as $index => $layer) {
            $groups['layer'.$index] = $layer;
        }

        return [
            'name' => $name,
            'category' => $category,
            'hdr' => false,
            'fpsLimit' => 60,
            'particles' => [
                // Groups are created first, so the base itself spawns nothing.
                'number' => ['value' => array_sum(array_map(fn (array $layer): int => $layer['number']['value'], $layers))],
                'move' => ['enable' => true],
                'groups' => $groups,
            ],
            'emitters' => $emitters,
        ];
    }

    /**
     * @param  string|list<string>  $colors
     * @param  float|array{min: float, max: float}  $size
     * @param  array<string, mixed>  ...$behaviours
     * @return array<string, mixed>
     */
    private static function layer(int $count, array $shape, string|array $colors, float|array $size, array ...$behaviours): array
    {
        $maxSize = is_array($size) ? $size['max'] : $size;
        $opacity = $maxSize <= self::SMALL_PARTICLE_MAX_SIZE ? self::SMALL_PARTICLE_OPACITY : self::BACKGROUND_OPACITY;

        return array_replace_recursive([
            'number' => ['value' => $count],
            'shape' => $shape,
            'paint' => [
                'color' => ['value' => $colors],
                'fill' => ['enable' => true, 'opacity' => $opacity],
                'stroke' => ['opacity' => $opacity],
            ],
            'size' => ['value' => $size],
            'move' => ['enable' => true, 'speed' => 1, 'outModes' => 'out'],
        ], ...$behaviours);
    }

    /**
     * @return array<string, mixed>
     */
    private static function glyphs(string ...$icons): array
    {
        return self::characters(array_map(fn (string $icon): string => self::ICONS[$icon], $icons), self::FONT_AWESOME_FAMILY, '900');
    }

    /**
     * @param  list<string>  $characters
     * @return array<string, mixed>
     */
    private static function characters(array $characters, string $font, string $weight = '400'): array
    {
        return [
            'type' => 'text',
            'options' => [
                'text' => ['value' => $characters, 'font' => $font, 'weight' => $weight],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function circle(): array
    {
        return ['type' => 'circle'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function square(): array
    {
        return ['type' => 'square'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function falling(float $minSpeed, float $maxSpeed): array
    {
        return self::drifting('bottom', $minSpeed, $maxSpeed);
    }

    /**
     * @return array<string, mixed>
     */
    private static function rising(float $minSpeed, float $maxSpeed): array
    {
        return self::drifting('top', $minSpeed, $maxSpeed);
    }

    /**
     * @return array<string, mixed>
     */
    private static function drifting(string $direction, float $minSpeed, float $maxSpeed): array
    {
        return ['move' => ['direction' => $direction, 'speed' => ['min' => $minSpeed, 'max' => $maxSpeed], 'straight' => false]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function wandering(float $speed): array
    {
        return ['move' => ['direction' => 'none', 'random' => true, 'speed' => $speed]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function still(): array
    {
        return ['move' => ['enable' => false]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function swaying(float $distance = 20, float $speed = 4): array
    {
        return ['wobble' => ['enable' => true, 'distance' => $distance, 'speed' => ['min' => -$speed, 'max' => $speed]]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function spinning(float $speed): array
    {
        return ['rotate' => ['value' => ['min' => 0, 'max' => 360], 'direction' => 'random', 'animation' => ['enable' => true, 'speed' => $speed]]];
    }

    /**
     * 3D flip around one axis, darkening the back side.
     *
     * @return array<string, mixed>
     */
    private static function flipping(float $minSpeed, float $maxSpeed, string $mode = 'vertical'): array
    {
        return ['roll' => ['enable' => true, 'mode' => $mode, 'speed' => ['min' => $minSpeed, 'max' => $maxSpeed], 'darken' => ['enable' => true, 'value' => 25]]];
    }

    /**
     * Paper-like flutter: confetti, petals, leaves.
     *
     * @return array<string, mixed>
     */
    private static function fluttering(): array
    {
        return [
            ...self::spinning(30),
            'tilt' => ['enable' => true, 'direction' => 'random', 'value' => ['min' => 0, 'max' => 360], 'animation' => ['enable' => true, 'speed' => 30]],
            'roll' => ['enable' => true, 'speed' => ['min' => 5, 'max' => 15], 'darken' => ['enable' => true, 'value' => 25]],
            'wobble' => ['enable' => true, 'distance' => 20, 'speed' => ['min' => -10, 'max' => 10]],
        ];
    }

    /**
     * Size breathing between the layer's min and max size.
     *
     * @return array<string, mixed>
     */
    private static function pulsing(float $speed): array
    {
        return ['size' => ['animation' => ['enable' => true, 'speed' => $speed, 'sync' => false]]];
    }

    /**
     * Every particle pulses together, like on a beat.
     *
     * @return array<string, mixed>
     */
    private static function beat(): array
    {
        return ['size' => ['animation' => ['enable' => true, 'speed' => 15, 'sync' => true]]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function opacity(float $min, float $max): array
    {
        $opacity = ['min' => $min, 'max' => $max];

        return ['paint' => ['fill' => ['opacity' => $opacity], 'stroke' => ['opacity' => $opacity]]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function fading(float $speed, float $max = 1): array
    {
        return ['opacity' => ['value' => ['min' => 0.35, 'max' => $max], 'animation' => ['enable' => true, 'speed' => $speed, 'sync' => false]]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function twinkling(): array
    {
        return self::fading(1.5);
    }

    /**
     * @return array<string, mixed>
     */
    private static function trembling(): array
    {
        return ['move' => ['vibrate' => true]];
    }

    /**
     * Drops accelerating downwards.
     *
     * @return array<string, mixed>
     */
    private static function dripping(): array
    {
        return ['move' => [
            'direction' => 'bottom',
            'speed' => ['min' => 0.5, 'max' => 1],
            'straight' => true,
            'gravity' => ['enable' => true, 'acceleration' => 2, 'maxSpeed' => 6],
        ]];
    }

    /**
     * Endless hops along the bottom of the screen.
     *
     * @return array<string, mixed>
     */
    private static function hopping(float $speed): array
    {
        return [
            'move' => [
                'direction' => 'right',
                'speed' => $speed,
                'straight' => true,
                'gravity' => ['enable' => true, 'acceleration' => 15, 'maxSpeed' => 40],
                'outModes' => ['default' => 'out', 'bottom' => 'bounce', 'top' => 'none'],
            ],
            'bounce' => ['vertical' => ['value' => 1]],
        ];
    }

    /**
     * Bouncing off the screen edges and off each other.
     *
     * @return array<string, mixed>
     */
    private static function pinball(): array
    {
        return [
            'move' => ['direction' => 'none', 'random' => true, 'speed' => 3, 'outModes' => 'bounce'],
            'collisions' => ['enable' => true, 'mode' => 'bounce'],
            ...self::spinning(5),
        ];
    }

    /**
     * Spiralling around the center of the screen.
     *
     * @return array<string, mixed>
     */
    private static function swirling(): array
    {
        return ['move' => [
            'direction' => 'none',
            'speed' => ['min' => 1, 'max' => 2],
            'spin' => ['enable' => true, 'acceleration' => ['min' => -0.1, 'max' => 0.1], 'position' => ['x' => 50, 'y' => 50]],
        ]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function ring(float $width): array
    {
        return ['paint' => ['fill' => ['enable' => false], 'stroke' => ['width' => $width]]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function outlined(string $color, float $width): array
    {
        return ['paint' => ['stroke' => ['color' => ['value' => $color], 'width' => $width]]];
    }

    /**
     * Throws particles up from the bottom of the screen, like a pan or a toaster.
     *
     * @param  array<string, mixed>  $particles
     * @return array<string, mixed>
     */
    private static function toss(array $particles, float $x, float $delay, int $quantity = 1): array
    {
        return [
            'direction' => 'top',
            'position' => ['x' => $x, 'y' => 100],
            'size' => ['width' => 0, 'height' => 0],
            'rate' => ['quantity' => $quantity, 'delay' => $delay],
            'particles' => array_replace_recursive($particles, ['move' => [
                'direction' => 'top',
                'speed' => ['min' => 18, 'max' => 24],
                'straight' => false,
                'angle' => ['value' => 20, 'offset' => 0],
                'gravity' => ['enable' => true, 'acceleration' => 9.81, 'maxSpeed' => 30],
                'outModes' => ['default' => 'destroy', 'top' => 'none'],
            ]]),
        ];
    }

    /**
     * Bursts of small particles popping up, like popcorn.
     *
     * @param  array<string, mixed>  $particles
     * @return array<string, mixed>
     */
    private static function pop(array $particles, float $x, float $delay = 0.15): array
    {
        return array_replace_recursive(self::toss($particles, $x, $delay, 3), [
            'particles' => ['move' => ['speed' => ['min' => 10, 'max' => 20], 'angle' => ['value' => 70]]],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function shootingStar(string $color): array
    {
        return [
            'direction' => 'bottom-right',
            'position' => ['x' => 20, 'y' => 0],
            'size' => ['width' => 40, 'height' => 0],
            'rate' => ['quantity' => 1, 'delay' => 7],
            'particles' => self::layer(0, self::glyphs('star'), $color, 8, self::opacity(0.7, 1), [
                'move' => ['direction' => 'bottom-right', 'speed' => 18, 'straight' => true, 'outModes' => 'destroy'],
            ]),
        ];
    }

    /**
     * Rockets launched from the bottom that burst into sparks.
     *
     * @param  list<string>  $colors
     * @return list<array<string, mixed>>
     */
    private static function fireworks(array $colors): array
    {
        return [[
            'direction' => 'top',
            'position' => ['x' => 50, 'y' => 100],
            'size' => ['width' => 70, 'height' => 0],
            'rate' => ['quantity' => 1, 'delay' => 0.8],
            'particles' => self::layer(0, self::circle(), $colors, 4, [
                'move' => ['direction' => 'top', 'speed' => ['min' => 14, 'max' => 20], 'straight' => true, 'outModes' => 'destroy'],
                'life' => ['count' => 1, 'duration' => ['value' => ['min' => 0.6, 'max' => 1]]],
                'destroy' => [
                    'mode' => 'split',
                    'split' => [
                        'count' => 1,
                        'factor' => ['value' => 1.2],
                        'rate' => ['value' => ['min' => 60, 'max' => 90]],
                        'particles' => [
                            'move' => [
                                'direction' => 'none',
                                'speed' => ['min' => 6, 'max' => 16],
                                'straight' => false,
                                'random' => true,
                                'decay' => 0.05,
                                'gravity' => ['enable' => true, 'acceleration' => 3, 'maxSpeed' => 10],
                                'outModes' => 'destroy',
                            ],
                            'life' => ['count' => 1, 'duration' => ['value' => ['min' => 1, 'max' => 1.8]]],
                        ],
                    ],
                ],
            ]),
        ]];
    }
}
