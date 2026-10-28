<?php

namespace App\Lib;

use App\Enums\DashboardStyleMode;
use App\Models\CalendarEvent;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Picks the dashboard style of a day: the one chosen in the admin, else the one the
 * event name suggests, else the one of the calendar (events added by the tenant,
 * festivities, then seasons).
 */
class DashboardStyle
{
    /**
     * Stored style that disables the automatic one.
     */
    public const NONE = 'none';

    /**
     * Open days of the canteen a period we choose runs for: before a dated event, after a start.
     * Official weeks and months keep their own length.
     */
    private const OPEN_DAYS = 2;

    /**
     * Open days for the biggest events: Christmas and New Year.
     */
    private const BIG_EVENT_OPEN_DAYS = 4;

    public static function resolve(?string $style, ?string $eventName, CarbonInterface $day, DashboardStyleMode $mode = DashboardStyleMode::Automatic, ?Tenant $tenant = null): ?string
    {
        return self::resolveWithReason($style, $eventName, $day, $mode, $tenant)['style'];
    }

    /**
     * The style of the day, why it was picked and the real date of that reason, to tell on the dashboard.
     * The source tells what gave the style: a choice made by hand ("Aucun style" included), the event name,
     * the calendar or, failing all of them, the burgers and fries day.
     *
     * @param  list<array<string, mixed>>|null  $calendar
     * @return array{style: ?string, reason: ?string, date: ?string, source: 'chosen'|'event'|'calendar'|'dishes'|null}
     */
    public static function resolveWithReason(?string $style, ?string $eventName, CarbonInterface $day, DashboardStyleMode $mode = DashboardStyleMode::Automatic, ?Tenant $tenant = null, ?array $calendar = null, bool $burgersAndFriesDay = false): array
    {
        $none = ['style' => null, 'reason' => null, 'date' => null, 'source' => null];
        if ($mode === DashboardStyleMode::Disabled) {
            return $none;
        }
        if ($style === self::NONE) {
            return [...$none, 'source' => 'chosen'];
        }

        // A style chosen by hand needs no explanation.
        if ($style !== null && $style !== '') {
            return array_key_exists($style, config('tsparticles.config', [])) ? [...$none, 'style' => $style, 'source' => 'chosen'] : $none;
        }

        if ($mode === DashboardStyleMode::Manual) {
            return $none;
        }

        if ($suggested = self::fromEventName($eventName)) {
            return ['style' => $suggested, 'reason' => 'Animation « '.trim($eventName).' »', 'date' => null, 'source' => 'event'];
        }

        $period = self::periodOn($day, $tenant, $calendar);
        if ($period) {
            return ['style' => $period['style'], 'reason' => $period['label'], 'date' => $period['detail'] ? Str::ucfirst($period['detail']) : null, 'source' => 'calendar'];
        }

        if ($burgersAndFriesDay) {
            return ['style' => 'burger', 'reason' => 'Jour des Burgers et des Frites', 'date' => null, 'source' => 'dishes'];
        }

        return $none;
    }

    /**
     * Name of a calendar period with its real dates, e.g. "Saint-Valentin (samedi 14 février)".
     *
     * @param  array<string, mixed>  $period
     */
    public static function describe(array $period): string
    {
        return $period['label'].($period['detail'] ? ' ('.$period['detail'].')' : '');
    }

    /**
     * "du 16 au 22 septembre", or a single date when both are the same day.
     */
    public static function formatRange(CarbonInterface $start, CarbonInterface $end): string
    {
        if ($start->equalTo($end)) {
            return self::formatDate($start, 'l j F');
        }

        return 'du '.self::formatDate($start, $start->month === $end->month ? 'j' : 'j F').' au '.self::formatDate($end, 'j F');
    }

    /**
     * The style whose longest keyword appears in the event name.
     */
    public static function fromEventName(?string $eventName): ?string
    {
        $text = ' '.self::normalize($eventName ?? '').' ';
        $bestStyle = null;
        $bestLength = 0;

        foreach (config('tsparticles.config', []) as $style => $options) {
            foreach ($options['keywords'] ?? [] as $keyword) {
                // Short keywords ("ia", "jo") must be whole words, longer ones may be a word start ("ital" → "italienne").
                $needle = ' '.$keyword.(strlen($keyword) <= 3 ? ' ' : '');
                if (strlen($keyword) > $bestLength && str_contains($text, $needle)) {
                    $bestStyle = $style;
                    $bestLength = strlen($keyword);
                }
            }
        }

        return $bestStyle;
    }

    public static function fromCalendar(CarbonInterface $day, ?Tenant $tenant = null): ?string
    {
        return self::periodOn($day, $tenant)['style'] ?? null;
    }

    /**
     * The calendar period that gives its style to a day, if any. Pass the year's
     * calendar when looking up many days, to compute it only once.
     *
     * @param  list<array<string, mixed>>|null  $calendar
     * @return array{style: string, label: string, start: CarbonImmutable, end: CarbonImmutable, season: bool, event: ?CalendarEvent}|null
     */
    public static function periodOn(CarbonInterface $day, ?Tenant $tenant = null, ?array $calendar = null): ?array
    {
        $day = CarbonImmutable::create($day->year, $day->month, $day->day);

        foreach ($calendar ?? self::calendar($day->year, $tenant) as $period) {
            if ($day->between($period['start'], $period['end'])) {
                return $period;
            }
        }

        return null;
    }

    /**
     * The calendar of a year, by priority: the first period containing a day gives its style.
     * A period runs on the days the style is shown, its detail tells the real date of the event.
     * Events added by the tenant come first, then festivity days, longer periods, and the
     * first week of each season.
     *
     * @return list<array{style: string, label: string, start: CarbonImmutable, end: CarbonImmutable, season: bool, event: ?CalendarEvent}>
     */
    public static function calendar(int $year, ?Tenant $tenant = null): array
    {
        $easter = self::easter($year);
        $date = fn (int $month, int $day): CarbonImmutable => CarbonImmutable::create($year, $month, $day);
        $period = fn (string $style, string $label, CarbonImmutable $start, ?CarbonImmutable $end = null, bool $season = false, ?string $detail = null): array => [
            'style' => $style,
            'label' => $label,
            'start' => $start,
            'end' => $end ?? $start,
            'season' => $season,
            'event' => null,
            'detail' => $detail,
        ];
        $day = fn (CarbonImmutable $day): string => self::formatDate($day, 'l j F');
        // A dated festivity, shown on the closest working day.
        $festivity = function (string $style, string $label, CarbonImmutable $date, bool $after = false) use ($period, $day): array {
            return $period($style, $label, $after ? self::observedAfter($date) : self::observed($date), detail: $day($date));
        };

        // Last Sunday of May, pushed back a week when it falls on Pentecost.
        $mothersDay = $date(5, 1)->lastOfMonth(CarbonInterface::SUNDAY);
        if ($mothersDay->equalTo($easter->addDays(49))) {
            $mothersDay = $mothersDay->addWeek();
        }

        // Paris Pride is on the last Saturday of June, its week runs Monday to Friday before.
        $parisPrideMarch = $date(6, 1)->lastOfMonth(CarbonInterface::SATURDAY);
        // Téléthon: first weekend of December.
        $telethon = $date(12, 1)->firstOfMonth(CarbonInterface::SATURDAY);
        // The SEEPH is held on the third week of November.
        $disabilityWeek = $date(11, 1)->nthOfMonth(3, CarbonInterface::MONDAY);

        // Shown during the first open days of the season, from $shownFrom when the start is taken by something else.
        $seasonStart = fn (string $style, string $label, CarbonImmutable $start, ?CarbonImmutable $shownFrom = null): array => $period($style, $label, $shownFrom ?? $start, self::openDaysFrom($shownFrom ?? $start), true, $day($start));
        // Announced on the open days before the event.
        $before = fn (string $style, string $label, CarbonImmutable $event, ?CarbonImmutable $end = null, int $openDays = self::OPEN_DAYS): array => $period($style, $label, self::openDaysUntil($event, $openDays), $end ?? $event, detail: $day($event));
        $newYearEnd = self::openDaysFrom($date(1, 2), self::BIG_EVENT_OPEN_DAYS - 1);
        $backToSchoolEnd = self::openDaysFrom($date(9, 1));

        $events = $tenant ? $tenant->calendarEvents()
            ->where('starts_on', '<=', $date(12, 31))
            ->where('ends_on', '>=', $date(1, 1))
            ->orderBy('starts_on')
            ->get()
            ->map(fn (CalendarEvent $event): array => [...$period($event->style, $event->label, $event->starts_on, $event->ends_on, detail: self::formatRange($event->starts_on, $event->ends_on)), 'event' => $event])
            ->all() : [];

        $festivities = [
            $festivity('crepes', 'Chandeleur', $date(2, 2)),
            $festivity('valentine', 'Saint-Valentin', $date(2, 14)),
            $period('carnival', 'Mardi gras', $easter->subDays(47), detail: $day($easter->subDays(47))),
            $festivity('womens_day', 'Journée internationale des droits des femmes', $date(3, 8)),
            $festivity('st_patrick', 'Saint-Patrick', $date(3, 17)),
            $festivity('april_fool', 'Poisson d\'avril', $date(4, 1)),
            $festivity('health_week', 'Journée mondiale de la santé', $date(4, 7)),
            $festivity('earth_day', 'Jour de la Terre', $date(4, 22)),
            // Moved forward rather than back, so it does not take a day from May 8th.
            $festivity('europe_day', 'Journée de l\'Europe', $date(5, 9), after: true),
            $festivity('earth_day', 'Journée mondiale de l\'environnement', $date(6, 5)),
            $festivity('blood_donation', 'Journée mondiale du donneur de sang', $date(6, 14)),
            $festivity('music_day', 'Fête de la musique', $date(6, 21)),
            $festivity('saint_jean', 'Feux de la Saint-Jean', $date(6, 24)),
            $festivity('orange_day', 'Journée internationale pour l\'élimination de la violence à l\'égard des femmes', $date(11, 25)),
            $festivity('st_nicolas', 'Saint-Nicolas', $date(12, 6)),
        ];
        // A Sunday festivity takes the last open day before it that no other festivity holds.
        $takenDays = array_map(fn (array $festivity): string => $festivity['start']->format('Y-m-d'), $festivities);
        $sundayFestivity = function (string $style, string $label, CarbonImmutable $sunday) use ($period, $day, $takenDays): array {
            $shownOn = self::observed($sunday);
            while (in_array($shownOn->format('Y-m-d'), $takenDays, true)) {
                $shownOn = self::observed($shownOn->subDay());
            }

            return $period($style, $label, $shownOn, detail: $day($sunday));
        };

        return [
            ...$events,
            ...$festivities,

            $period('new_year', 'Nouvel an', $date(1, 1), $newYearEnd, detail: $day($date(1, 1))),
            $period('galette', 'Galette des rois', $newYearEnd->addDay(), self::openDaysFrom($newYearEnd->addDay()), detail: $day($date(1, 6))),
            $before('easter', 'Pâques', $easter, $easter->addDay()),
            // Public holidays are announced on the open day before only.
            $before('may_day', 'Journée internationale des travailleurs', $date(5, 1), openDays: 1),
            $before('remembrance', 'Victoire du 8 mai 1945', $date(5, 8), openDays: 1),
            $sundayFestivity('mothers_day', 'Fête des mères', $mothersDay),
            $sundayFestivity('fathers_day', 'Fête des pères', $date(6, 1)->nthOfMonth(3, CarbonInterface::SUNDAY)),
            $period('pride', 'Mois des fiertés', $parisPrideMarch->subDays(5), $parisPrideMarch->subDay(), detail: 'marche des fiertés le '.$day($parisPrideMarch)),
            $before('bastille_day', 'Fête nationale', $date(7, 14), openDays: 1),
            $period('back_to_school', 'Rentrée', $date(9, 1), $backToSchoolEnd),
            $period('mobility_week', 'Semaine européenne de la mobilité', $date(9, 16), $date(9, 22), detail: self::formatRange($date(9, 16), $date(9, 22))),
            $period('cyber_month', 'Cybermois, mois européen de la cybersécurité', $date(10, 1), self::openDaysFrom($date(10, 1), 2), detail: 'tout le mois d\'octobre'),
            $period('pink_october', 'Octobre rose', $date(10, 15), $date(10, 21)),
            $before('halloween', 'Halloween', $date(10, 31)),
            $before('remembrance', 'Armistice du 11 novembre 1918', $date(11, 11), openDays: 1),
            $period('disability_week', 'Semaine européenne pour l\'emploi des personnes handicapées', $disabilityWeek, $disabilityWeek->addDays(4), detail: self::formatRange($disabilityWeek, $disabilityWeek->addDays(6))),
            // First weekend of December, announced on the Thursday and Friday before.
            $period('telethon', 'Téléthon', $telethon->subDays(2), $telethon->subDay(), detail: 'week-end du '.$day($telethon)),
            $before('christmas', 'Noël', $date(12, 25), openDays: self::BIG_EVENT_OPEN_DAYS),
            $period('new_year', 'Nouvel an', self::observed($date(12, 31)), $date(12, 31), detail: self::formatDate($date(1, 1)->addYear(), 'l j F Y')),

            $seasonStart('spring', 'Printemps météorologique', $date(3, 1)),
            $seasonStart('summer', 'Été météorologique', $date(6, 1)),
            // The first days of September belong to the back to school period.
            $seasonStart('autumn', 'Automne météorologique', $date(9, 1), $backToSchoolEnd->addDay()),
            $seasonStart('winter', 'Hiver météorologique', $date(12, 1)),
        ];
    }

    /**
     * Carbon's translatedFormat(), writing the first day of the month "1er" as French does.
     */
    public static function formatDate(CarbonInterface $day, string $format = 'l j F'): string
    {
        return $day->translatedFormat($day->day === 1 ? str_replace('j', '\1\e\r', $format) : $format);
    }

    /**
     * Lowercase, without accents nor punctuation, as the keywords are written.
     */
    public static function normalize(string $text): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($text))));
    }

    /**
     * The canteen is closed on weekends and public holidays: a festivity falling on one
     * is celebrated on the working day before.
     */
    private static function observed(CarbonImmutable $day): CarbonImmutable
    {
        while ($day->isWeekend() || self::isPublicHoliday($day)) {
            $day = $day->subDay();
        }

        return $day;
    }

    /**
     * First day of the $count open days of the canteen ending on $day.
     */
    private static function openDaysUntil(CarbonImmutable $day, int $count = self::OPEN_DAYS): CarbonImmutable
    {
        $day = self::observed($day);
        for ($i = 1; $i < $count; $i++) {
            $day = self::observed($day->subDay());
        }

        return $day;
    }

    /**
     * Last day of the $count open days of the canteen starting on $day.
     */
    private static function openDaysFrom(CarbonImmutable $day, int $count = self::OPEN_DAYS): CarbonImmutable
    {
        $day = self::observedAfter($day);
        for ($i = 1; $i < $count; $i++) {
            $day = self::observedAfter($day->addDay());
        }

        return $day;
    }

    /**
     * Same as observed(), but on the next working day.
     */
    private static function observedAfter(CarbonImmutable $day): CarbonImmutable
    {
        while ($day->isWeekend() || self::isPublicHoliday($day)) {
            $day = $day->addDay();
        }

        return $day;
    }

    /**
     * French public holidays the canteen is closed on.
     */
    public static function isPublicHoliday(CarbonInterface $day): bool
    {
        return self::publicHolidayName($day) !== null;
    }

    /**
     * Name of the French public holiday falling on that day, if any. Whit Monday is left out:
     * it is worked as the solidarity day and the canteen is open.
     */
    public static function publicHolidayName(CarbonInterface $day): ?string
    {
        $easter = self::easter($day->year);
        $holidays = [
            '01-01' => 'Jour de l\'an',
            $easter->addDay()->format('m-d') => 'Lundi de Pâques',
            '05-01' => 'Journée internationale des travailleurs',
            '05-08' => 'Victoire du 8 mai 1945',
            $easter->addDays(39)->format('m-d') => 'Ascension',
            '07-14' => 'Fête nationale',
            '08-15' => 'Assomption',
            '11-01' => 'Toussaint',
            '11-11' => 'Armistice du 11 novembre 1918',
            '12-25' => 'Noël',
        ];

        return $holidays[$day->format('m-d')] ?? null;
    }

    /**
     * Whit Monday, worked as the solidarity day.
     */
    public static function isSolidarityDay(CarbonInterface $day): bool
    {
        return $day->format('m-d') === self::easter($day->year)->addDays(50)->format('m-d');
    }

    /**
     * Easter Sunday (anonymous Gregorian algorithm), ext-calendar not being required.
     */
    private static function easter(int $year): CarbonImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $n = $h + $l - 7 * $m + 114;

        return CarbonImmutable::create($year, intdiv($n, 31), $n % 31 + 1);
    }
}
