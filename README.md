#Glicko2-Team


A PHP glicko2 Composite Opponent implementation

The repository is build on https://github.com/diegobanos/php-glicko2



## How to use

```php
use Diegobanos\Glicko2\Rating\Rating;
use Diegobanos\Glicko2\Glicko2;


$team = [
    new Rating(1500,350),
    new Rating(1500,350),
    new Rating(1500,350),
]
$team1 = [
    new Rating(1500,350),
    new Rating(1500,350),
    new Rating(1500,350),
]
// $team won
$Ratings = Glicko2::match($team, $team1, 1, 0)

//You can add only 1 player to each team, and it will act the same as original glicko2
```

You can also create your own `Rating` class that implements `Diegobanos\Glicko2\Rating\Rating\RatingInterface`.




The algorithm implemented on this project is described in the following [PDF](http://www.glicko.net/glicko/glicko2.pdf).

It uses Composite team Update method from [PDF](https://rhetoricstudios.com/downloads/AbstractingGlicko2ForTeamGames.pdf).
