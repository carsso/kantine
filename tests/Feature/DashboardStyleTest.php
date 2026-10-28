<?php

namespace Tests\Feature;

use App\Enums\DashboardStyleMode;
use App\Lib\DashboardStyle;
use App\Models\CalendarEvent;
use App\Models\Tenant;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardStyleTest extends TestCase
{
    /**
     * @return array<string, array{string, ?string}>
     */
    public static function calendarDays(): array
    {
        return [
            'new year' => ['2026-01-01', 'new_year'],
            'new year lasts until the 5th' => ['2026-01-05', 'new_year'],
            'new year lasts until the 6th' => ['2026-01-06', 'new_year'],
            'galette' => ['2026-01-07', 'galette'],
            'galette lasts two open days' => ['2026-01-08', 'galette'],
            'no style after the galette' => ['2026-01-09', null],
            'no style right after the galette' => ['2026-01-12', null],
            'plain winter day' => ['2026-01-20', null],
            'candlemas' => ['2026-02-02', 'crepes'],
            'valentine on a saturday, celebrated on friday' => ['2026-02-13', 'valentine'],
            'mardi gras' => ['2026-02-17', 'carnival'],
            'start of spring' => ['2026-03-03', 'spring'],
            'summer start shows for five days' => ['2026-06-08', null],
            'mardi gras wins over the start of spring' => ['2025-03-04', 'carnival'],
            'saint patrick wins over an early easter week' => ['2027-03-17', 'st_patrick'],
            'easter week' => ['2026-04-02', 'easter'],
            'easter is announced two open days before' => ['2026-03-31', null],
            'easter monday' => ['2026-04-06', 'easter'],
            'april fool wins over easter week' => ['2026-04-01', 'april_fool'],
            'april fool on easter monday, celebrated on friday' => ['2024-03-29', 'april_fool'],
            'earth day on easter monday, celebrated on friday' => ['2030-04-19', 'earth_day'],
            'no astronomical spring' => ['2026-03-23', null],
            'world health day' => ['2026-04-07', 'health_week'],
            'europe day on a saturday, celebrated on the next monday' => ['2026-05-11', 'europe_day'],
            'world environment day' => ['2026-06-05', 'earth_day'],
            'saint jean wins over pride week' => ['2026-06-24', 'saint_jean'],
            'victory day, on the open day before' => ['2026-05-07', 'remembrance'],
            'victory day is not announced earlier' => ['2026-05-06', null],
            'may day, on the open day before' => ['2026-04-30', 'may_day'],
            'may day is not announced earlier' => ['2026-04-29', null],
            'plain spring day' => ['2026-05-12', null],
            'before mothers day' => ['2026-05-29', 'mothers_day'],
            'mothers day pushed back by pentecost' => ['2020-06-04', 'mothers_day'],
            'blood donation day on a sunday, celebrated on friday' => ['2026-06-12', 'blood_donation'],
            'fathers day moved to thursday, friday being music day' => ['2026-06-18', 'fathers_day'],
            'fathers day is a single day' => ['2026-06-17', null],
            'mothers day is a single day' => ['2026-05-28', null],
            'music day on a sunday wins over fathers day' => ['2026-06-19', 'music_day'],
            'start of summer' => ['2026-06-02', 'summer'],
            'pride week' => ['2026-06-23', 'pride'],
            'plain summer day' => ['2026-08-10', null],
            'no style around august 15th' => ['2026-08-14', null],
            'pride week, before the paris march' => ['2026-06-25', 'pride'],
            'bastille day, on the open day before' => ['2026-07-13', 'bastille_day'],
            'bastille day is not announced earlier' => ['2026-07-10', null],
            'back to school' => ['2026-09-01', 'back_to_school'],
            'back to school lasts two open days' => ['2026-09-02', 'back_to_school'],
            'start of autumn, after back to school' => ['2026-09-03', 'autumn'],
            'european mobility week' => ['2026-09-17', 'mobility_week'],
            'mobility week ends on the 22nd' => ['2026-09-22', 'mobility_week'],
            'no astronomical autumn' => ['2026-09-24', null],
            'cybersecurity month, two open days' => ['2026-10-02', 'cyber_month'],
            'cybersecurity month is short' => ['2026-10-05', null],
            'plain october day' => ['2026-10-12', null],
            'pink october' => ['2026-10-20', 'pink_october'],
            'pink october lasts a week' => ['2026-10-22', null],
            'halloween week' => ['2026-10-29', 'halloween'],
            'halloween is announced two open days before' => ['2026-10-28', null],
            'halloween ends on the 31st' => ['2026-11-02', null],
            'armistice, on the open day before' => ['2026-11-10', 'remembrance'],
            'armistice is not announced earlier' => ['2026-11-09', null],
            'european disability employment week' => ['2026-11-19', 'disability_week'],
            'day against violence against women' => ['2026-11-25', 'orange_day'],
            'plain november day' => ['2026-11-26', null],
            'telethon' => ['2026-12-03', 'telethon'],
            'start of winter' => ['2026-12-02', 'winter'],
            'saint nicolas on a sunday, celebrated on friday' => ['2026-12-04', 'st_nicolas'],
            'no christmas market' => ['2026-12-08', null],
            'christmas is announced four open days before' => ['2026-12-21', 'christmas'],
            'no style before christmas' => ['2026-12-18', null],
            'no style between christmas and new year' => ['2026-12-30', null],
            'new year is announced the day before' => ['2026-12-31', 'new_year'],
        ];
    }

    #[DataProvider('calendarDays')]
    public function test_the_calendar_picks_the_festivity_or_the_season(string $day, ?string $style): void
    {
        $this->assertSame($style, DashboardStyle::fromCalendar(Carbon::parse($day)));
    }

    /**
     * @return array<string, array{string, ?string}>
     */
    public static function eventNames(): array
    {
        return [
            'country' => ['PORTUGAL', 'portugal'],
            'adjective' => ['MEXICAIN', 'mexican'],
            'word start' => ['A l’italienne', 'italian'],
            'related dish' => ['Tartiflette', 'mountain'],
            'accents' => ['Crémaillère', 'housewarming'],
            'typo seen in production' => ['Haloween', 'halloween'],
            'longest keyword wins' => ['Pain perdu', 'french_toast'],
            'surrounded by emojis' => ['🍔 Jounée mondiale du burger 🍔', 'burger'],
            'short keyword is a whole word' => ['Journée IA', 'ai'],
            'short keyword is not a word start' => ['Iaourt', null],
            'unknown' => ['Les tuches', null],
        ];
    }

    #[DataProvider('eventNames')]
    public function test_the_event_name_suggests_a_style(string $eventName, ?string $style): void
    {
        $this->assertSame($style, DashboardStyle::fromEventName($eventName));
    }

    public function test_the_chosen_style_comes_first(): void
    {
        $this->assertSame('snow', DashboardStyle::resolve('snow', 'Tartiflette', Carbon::parse('2026-10-29')));
    }

    public function test_the_periods_we_choose_last_two_open_days_but_christmas_and_new_year(): void
    {
        $officialWeeksAndMonths = ['pride', 'mobility_week', 'pink_october', 'disability_week'];
        foreach (range(2025, 2035) as $year) {
            foreach (DashboardStyle::calendar($year) as $period) {
                if (in_array($period['style'], $officialWeeksAndMonths, true)) {
                    continue;
                }
                $openDays = 0;
                for ($day = $period['start']; $day->lte($period['end']); $day = $day->addDay()) {
                    $openDays += ($day->isWeekend() || DashboardStyle::isPublicHoliday($day)) ? 0 : 1;
                }
                $this->assertLessThanOrEqual(in_array($period['style'], ['christmas', 'new_year'], true) ? 4 : 2, $openDays, "{$period['label']} {$year}");
                $this->assertGreaterThanOrEqual(1, $openDays, "{$period['label']} {$year}");
            }
        }
    }

    public function test_it_knows_the_french_public_holidays(): void
    {
        foreach (['2026-01-01', '2026-04-06', '2026-05-01', '2026-05-08', '2026-05-14', '2026-07-14', '2026-08-15', '2026-11-01', '2026-11-11', '2026-12-25'] as $holiday) {
            $this->assertTrue(DashboardStyle::isPublicHoliday(Carbon::parse($holiday)), "{$holiday} is a public holiday");
        }
        $this->assertFalse(DashboardStyle::isPublicHoliday(Carbon::parse('2026-04-03')), 'Good Friday is not a public holiday');
        $this->assertFalse(DashboardStyle::isPublicHoliday(Carbon::parse('2026-05-25')), 'Whit Monday is worked as the solidarity day');
        $this->assertTrue(DashboardStyle::isSolidarityDay(Carbon::parse('2026-05-25')));
        $this->assertSame('Ascension', DashboardStyle::publicHolidayName(Carbon::parse('2026-05-14')));
        $this->assertNull(DashboardStyle::publicHolidayName(Carbon::parse('2026-05-12')));
    }

    public function test_the_events_of_the_tenant_come_first_in_the_calendar(): void
    {
        $tenant = Tenant::factory()->create();
        CalendarEvent::factory()->create(['tenant_id' => $tenant->id, 'label' => 'Tournoi', 'style' => 'tennis', 'starts_on' => '2026-06-15', 'ends_on' => '2026-06-21']);

        $this->assertSame(['style' => 'tennis', 'reason' => 'Tournoi', 'date' => 'Du 15 au 21 juin', 'source' => 'calendar'], DashboardStyle::resolveWithReason(null, null, Carbon::parse('2026-06-19'), tenant: $tenant));

        $this->assertSame('tennis', DashboardStyle::fromCalendar(Carbon::parse('2026-06-19'), $tenant));
        $this->assertSame('music_day', DashboardStyle::fromCalendar(Carbon::parse('2026-06-19')));
        $this->assertSame('music_day', DashboardStyle::fromCalendar(Carbon::parse('2026-06-19'), Tenant::factory()->create()));
    }

    /**
     * @return array<string, array{?string, ?string, string, ?string, ?string}>
     */
    public static function reasons(): array
    {
        return [
            'chosen style' => ['snow', 'Tartiflette', '2026-10-29', null, null],
            'event name' => [null, 'Tartiflette', '2026-10-12', 'Animation « Tartiflette »', null],
            'festivity on its day' => [null, null, '2026-02-02', 'Chandeleur', 'Lundi 2 février'],
            'festivity shown before its day' => [null, null, '2026-03-06', 'Journée internationale des droits des femmes', 'Dimanche 8 mars'],
            'galette on the day of the epiphany' => [null, null, '2026-01-07', 'Galette des rois', 'Mardi 6 janvier'],
            'easter week' => [null, null, '2026-04-02', 'Pâques', 'Dimanche 5 avril'],
            'christmas' => [null, null, '2026-12-22', 'Noël', 'Vendredi 25 décembre'],
            'meteorological autumn shown after back to school' => [null, null, '2026-09-03', 'Automne météorologique', 'Mardi 1er septembre'],
            'period without a real date' => [null, null, '2026-10-20', 'Octobre rose', null],
            'festivity moved to the friday before' => [null, null, '2026-02-13', 'Saint-Valentin', 'Samedi 14 février'],
            'festivity moved to the monday after' => [null, null, '2026-05-11', 'Journée de l\'Europe', 'Samedi 9 mai'],
            'period' => [null, null, '2026-09-17', 'Semaine européenne de la mobilité', 'Du 16 au 22 septembre'],
            'days before a sunday' => [null, null, '2026-05-29', 'Fête des mères', 'Dimanche 31 mai'],
            'weekend festivity' => [null, null, '2026-12-03', 'Téléthon', 'Week-end du samedi 5 décembre'],
            'european cybersecurity month' => [null, null, '2026-10-01', 'Cybermois, mois européen de la cybersécurité', 'Tout le mois d\'octobre'],
            'public holiday on the open day before' => [null, null, '2026-04-30', 'Journée internationale des travailleurs', 'Vendredi 1er mai'],
            'pride' => [null, null, '2026-06-25', 'Mois des fiertés', 'Marche des fiertés le samedi 27 juin'],
        ];
    }

    #[DataProvider('reasons')]
    public function test_it_tells_why_the_style_was_picked(?string $style, ?string $eventName, string $day, ?string $reason, ?string $date): void
    {
        $resolved = DashboardStyle::resolveWithReason($style, $eventName, Carbon::parse($day));

        $this->assertSame($reason, $resolved['reason']);
        $this->assertSame($date, $resolved['date']);
    }

    public function test_the_burgers_and_fries_day_only_comes_when_nothing_else_does(): void
    {
        $this->assertSame(['style' => 'burger', 'reason' => 'Jour des Burgers et des Frites', 'date' => null, 'source' => 'dishes'], DashboardStyle::resolveWithReason(null, null, Carbon::parse('2026-10-12'), burgersAndFriesDay: true));
        $this->assertSame('halloween', DashboardStyle::resolveWithReason(null, null, Carbon::parse('2026-10-30'), burgersAndFriesDay: true)['style']);
        $this->assertSame('mountain', DashboardStyle::resolveWithReason(null, 'Tartiflette', Carbon::parse('2026-10-12'), burgersAndFriesDay: true)['style']);
        $this->assertNull(DashboardStyle::resolveWithReason(DashboardStyle::NONE, null, Carbon::parse('2026-10-12'), burgersAndFriesDay: true)['style']);
        $this->assertNull(DashboardStyle::resolveWithReason(null, null, Carbon::parse('2026-10-12'), DashboardStyleMode::Manual, burgersAndFriesDay: true)['style']);
    }

    public function test_it_writes_the_first_day_of_the_month_the_french_way(): void
    {
        $this->assertSame('mercredi 1er avril', DashboardStyle::formatDate(Carbon::parse('2026-04-01')));
        $this->assertSame('1er novembre 2026', DashboardStyle::formatDate(Carbon::parse('2026-11-01'), 'j F Y'));
        $this->assertSame('jeudi 2 avril', DashboardStyle::formatDate(Carbon::parse('2026-04-02')));
    }

    public function test_there_is_no_reason_without_style(): void
    {
        $this->assertSame(['style' => null, 'reason' => null, 'date' => null, 'source' => null], DashboardStyle::resolveWithReason(null, null, Carbon::parse('2026-10-12')));
        $this->assertSame(['style' => null, 'reason' => null, 'date' => null, 'source' => null], DashboardStyle::resolveWithReason('snow', null, Carbon::parse('2026-10-12'), DashboardStyleMode::Disabled));
        $this->assertSame(['style' => null, 'reason' => null, 'date' => null, 'source' => 'chosen'], DashboardStyle::resolveWithReason(DashboardStyle::NONE, null, Carbon::parse('2026-10-29')));
    }

    public function test_the_event_name_comes_before_the_calendar(): void
    {
        $this->assertSame('mountain', DashboardStyle::resolve(null, 'Tartiflette', Carbon::parse('2026-10-29')));
        $this->assertSame('halloween', DashboardStyle::resolve('', 'Les tuches', Carbon::parse('2026-10-29')));
    }

    public function test_a_plain_day_has_no_automatic_style(): void
    {
        $this->assertNull(DashboardStyle::resolve(null, 'Les tuches', Carbon::parse('2026-10-12')));
    }

    public function test_the_automatic_style_can_be_disabled(): void
    {
        $this->assertNull(DashboardStyle::resolve(DashboardStyle::NONE, 'Tartiflette', Carbon::parse('2026-10-29')));
    }

    public function test_the_manual_mode_only_keeps_the_chosen_style(): void
    {
        $this->assertSame('snow', DashboardStyle::resolve('snow', null, Carbon::parse('2026-10-29'), DashboardStyleMode::Manual));
        $this->assertNull(DashboardStyle::resolve(null, 'Tartiflette', Carbon::parse('2026-10-29'), DashboardStyleMode::Manual));
    }

    public function test_the_disabled_mode_shows_nothing(): void
    {
        $this->assertNull(DashboardStyle::resolve('snow', 'Tartiflette', Carbon::parse('2026-10-29'), DashboardStyleMode::Disabled));
    }

    public function test_an_unknown_style_shows_nothing(): void
    {
        $this->assertNull(DashboardStyle::resolve('disco', null, Carbon::parse('2026-10-29')));
    }
}
