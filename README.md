# Sumo reporter

![Code coverage badge](https://github.com/stuartmcgill/sumo-reporter/blob/image-data/coverage.svg)

This console application uses the [Sumo API](https://sumo-api.com/) to produce formatted reports.

Currently it can show ordered wrestler 'streaks' i.e. how many bouts wrestlers have won (or lost) in
a row, which may of course stretch across successive bashos.

# Usage

```
src/run.php report:streaks [YYYY-MM] 
```

If a date (e.g. 2023-03) is not supplied then the streaks will be calculated starting from the most recent (or
in-progress) basho.

# Sample output

```
 src/run.php report:streaks                                                                                                                      8.2s  2023-04-16 21:22

Downloading wrestler streaks...
===============================

Winning
-------

+---------------+--------------------+---------+-------------+----------------+
| Name          | Rank               | Type    | Streak size | Still active?  |
+---------------+--------------------+---------+-------------+----------------+
| Toshunryu     | Sandanme 24 West   | Winning | 9           |                |
| Ichinojo      | Juryo 3 East       | Winning | 8           |                |
| Kiribayama    | Sekiwake 2 East    | Winning | 8           |                |
| Kiyonoumi     | Sandanme 80 East   | Winning | 8           |                |
| Asahakuryu    | Jonokuchi 11 West  | Winning | 7           |                |
| Ryuo          | Makushita 26 East  | Winning | 7           |                |
| Suguro        | Jonidan 60 West    | Winning | 7           |                |
| Fukai         | Makushita 40 West  | Winning | 6           |                |
| Kazenoumi     | Jonidan 32 East    | Winning | 6           |                |
| Kitanosho     | Jonidan 100 East   | Winning | 6           |                |
| Takahashi     | Makushita 33 West  | Winning | 6           |                |
| Takarafuji    | Maegashira 12 West | Winning | 6           |                |
| Anzakura      | Jonidan 99 West    | Winning | 5           |                |
| Gonoumi       | Jonokuchi 13 West  | Winning | 5           |                |
| Gonowaka      | Jonidan 68 West    | Winning | 5           |                |
| Hamasaki      | Jonidan 98 West    | Winning | 5           |                |
| Kobayashi     | Jonokuchi 14 East  | Winning | 5           |                |
| Kyokutaisei   | Sandanme 55 West   | Winning | 5           |                |
```
