# Sumo reporter

![Code coverage badge](https://github.com/stuartmcgill/sumo-reporter/blob/image-data/coverage.svg)

This console application uses the [Sumo API](https://sumo-api.com/) to produce formatted reports.

Currently it can show ordered wrestler 'streaks' i.e. how many bouts wrestlers have won (or lost) in
a row, which may of course stretch across successive bashos. (Currently data is only available from the March 2023 basho)
onwards.

# Usage

```
src/run.php report:streaks [YYYY-MM] 
```

If a date (e.g. 2023-03) is not supplied then the streaks will be calculated starting from the most recent (or
in-progress) basho.

# Sample output
