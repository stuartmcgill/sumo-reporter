# Sumo reporter

![Code coverage badge](https://github.com/stuartmcgill/sumo-reporter/blob/image-data/coverage.svg)

This console application uses the [Sumo API](https://sumo-api.com/) to produce formatted reports.

The following reports are provided:

- Ordered wrestler 'streaks' i.e. how many bouts wrestlers have won (or lost) in
a row, which may of course stretch across successive bashos
- Makuuchi consecutive match tracker i.e. how many successive bouts (regardless of win or loss) each
wrestler has fought

# Dependencies

_Sumo reporter_ is a [Symfony Console](https://symfony.com/doc/current/components/console.html) app
and uses [Laminas Service Manager](https://docs.laminas.dev/laminas-servicemanager/) for dependency
injection (in particular the [Reflection-based Abstract Factory](https://docs.laminas.dev/laminas-servicemanager/reflection-abstract-factory/)).

# Usage

## Streaks

```
src/run.php report:streaks [YYYY-MM] [output.csv]
```

If a date (e.g. 2023-03) is not supplied then the streaks will be calculated starting from the most recent (or
in-progress) basho.

This report is used to maintain the Sumo Forum [winning and losing streaks](http://www.sumoforum.net/forums/topic/42758-winning-and-losing-streaks/) thread.

### Sample output

```
 src/run.php report:streaks

Downloading wrestler streaks...
===============================

Winning
-------

+---------------+--------------------+---------+-------------+--------------+
| Name          | Rank               | Type    | Streak size | Unblemished? |
+---------------+--------------------+---------+-------------+--------------+
| Toshunryu     | Sandanme 24 West   | Winning | 9           |              |
| Kiribayama    | Sekiwake 2 East    | Winning | 8           |              |
| Ichinojo      | Juryo 3 East       | Winning | 8           |              |
| Kiyonoumi     | Sandanme 80 East   | Winning | 8           |              |
| Ryuo          | Makushita 26 East  | Winning | 7           |              |
| Suguro        | Jonidan 60 West    | Winning | 7           |              |
| Asahakuryu    | Jonokuchi 11 West  | Winning | 7           | Yes          |
| Takarafuji    | Maegashira 12 West | Winning | 6           |              |
| Takahashi     | Makushita 33 West  | Winning | 6           |              |
| Fukai         | Makushita 40 West  | Winning | 6           |              |
| Kazenoumi     | Jonidan 32 East    | Winning | 6           |              |
| Kitanosho     | Jonidan 100 East   | Winning | 6           |              |
| Nabatame      | Makushita 24 West  | Winning | 5           |              |
| Narutaki      | Makushita 29 East  | Winning | 5           |              |
| Tokisoma      | Sandanme 29 West   | Winning | 5           |              |
| Marusho       | Sandanme 42 East   | Winning | 5           |              |
| Kyokutaisei   | Sandanme 55 West   | Winning | 5           |              |
```

## Makuuchi consecutive match tracker

``` 
src/run.php report:consecutivematchtracker [YYYY-MM] [output.csv] [--covid-breaks-run]
```

If a date (e.g. 2024-01) is not supplied then the tracker will be started from the most recent basho.

If the `--covid-breaks-run` option is set then COVID-enforced kyujos will cause the run to end. The
default behaviour (i.e. with no option set) is to ignore COVID-enforced kyujos and allow the run to
continue.

This report is used to maintain the Sumo Forum [Makuuchi consecutive match tracker](http://www.sumoforum.net/forums/topic/36454-makuuchi-consecutive-match-tracker/?page=3) thread.

### Sample output

```
 src/run.php report:consecutivematchtracker 2024-01

Calculating consecutive matches...
==================================

Consecutive matches in Makuuchi
-------------------------------

+--------------+-------------------+---------+--------------------+
| Name         | Number of matches | Since   | Current rank       |
+--------------+-------------------+---------+--------------------+
| Takarafuji   | 990               | 2013-01 | Juryo 1 West       |
| Tamawashi    | 942               | 2013-07 | Maegashira 7 West  |
| Daieisho     | 612               | 2017-03 | Sekiwake 1 East    |
| Mitakeumi    | 431               | 2019-01 | Maegashira 10 West |
| Meisei       | 315               | 2020-09 | Maegashira 2 West  |
| Tobizaru     | 312               | 2020-09 | Maegashira 4 East  |
| Shodai       | 285               | 2021-01 | Maegashira 10 East |
| Sadanoumi    | 210               | 2021-11 | Maegashira 11 West |
| Kotonowaka   | 205               | 2021-11 | Ozeki 2 West       |
...
+--------------+-------------------+---------+--------------------+
```
