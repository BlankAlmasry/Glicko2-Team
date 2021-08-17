<?php
namespace Blankalmasry\Glicko2;

use Blankalmasry\Glicko2\Rating\Rating;
use Blankalmasry\Glicko2\Rating\RatingInterface;
use Blankalmasry\Glicko2\Rating\Transformer;
use Blankalmasry\Glicko2\Result\Result;
use Blankalmasry\Glicko2\Volatility\Calculator;

class Glicko2
{
    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @var Transformer
     */
    private $transformer;

    public function __construct(float $tau = 0.5, float $tol = 0.000001)
    {
        $this->calculator = new Calculator($tau, $tol);
        $this->transformer = new Transformer;
    }

    public function calculateRating(RatingInterface $rating, array $results): array
    {
        $normalizedRating = $this->transformer->normalizeRating($rating);
        $normalizedResults = [];

        foreach ($results as $result) {
            $normalizedResults[] = new Result($this->transformer->normalizeRating($result->getRating()), $result->getResult());
        }

        $funcResults = [];

        foreach ($normalizedResults as $normalizedResult) {
            $funcResults[] = [
                'u' => $normalizedResult->getRating()->getRating(),
                'o' => $normalizedResult->getRating()->getRatingDeviation(),
                'g' => $this->g($normalizedResult->getRating()->getRatingDeviation()),
                'E' => $this->E($normalizedRating->getRating(), $normalizedResult->getRating()->getRating(), $normalizedResult->getRating()->getRatingDeviation()),
                's' => $normalizedResult->getResult()
            ];
        }

        $v = $this->v($funcResults);
        $t = $this->t($v, $funcResults);

        $newVolatility = $this->calculator->calculateVolatility($normalizedRating->getVolatility(), $t, $normalizedRating->getRatingDeviation(), $v);
        $newRatingDeviationScore = $this->calculateRatingDeviation($normalizedRating->getRatingDeviation(), $v, $newVolatility);
        $newRatingScore = $this->calculateNewRating($normalizedRating->getRating(), $newRatingDeviationScore, $funcResults);

        $newRating = new Rating($newRatingScore, $newRatingDeviationScore, $newVolatility);

        return $this->transformer->standardizeRating($newRating);
    }

    private function calculateNewRating(float $rating, float $newRatingDeviation, array $funcResults): float
    {
        return $rating + pow($newRatingDeviation, 2) * array_sum(array_map(function($r) { return $r['g'] * ($r['s'] - $r['E']); }, $funcResults));
    }

    private function preRatingDeviation(float $ratingDeviation, float $newVolatility): float
    {
        return sqrt(pow($ratingDeviation, 2) + pow($newVolatility, 2));
    }

    private function calculateRatingDeviation(float $ratingDeviation, float $v, float $newVolatility): float
    {
        return 1 / sqrt((1 / pow($this->preRatingDeviation($ratingDeviation, $newVolatility), 2)) + (1 / $v));
    }

    private function g(float $ratingDeviation): float
    {
        return 1 / sqrt(1 + (3 * pow($ratingDeviation, 2))/pow(pi(), 2));
    }

    private function E(float $rating, float $ratingj, float $rdj): float
    {
        return 1 / (1 + exp(-$this->g($rdj) * ($rating - $ratingj)));
    }

    private function v(array $funcResults): float
    {
        return pow(array_sum(array_map(function($r) { return pow($r['g'], 2) * $r['E'] * (1 - $r['E']);}, $funcResults)),-1);
    }

    private function t(float $v, array $funcResults): float
    {
        return $v * array_sum(array_map(function($r) { return $r['g'] * ($r['s'] - $r['E']); }, $funcResults));
    }


    public static function match(array $team1, array $team2, int $int1, int $int2, $updateMethod = "compositeOpponent")
    {

       return self::$updateMethod($team1, $team2, $int1, $int2);


    }

    public static function compositeOpponent($team1, $team2, $int1, $int2)
    {
        $team1Average = [0,1];
        foreach ($team1 as $team){
            $team1Average[0] += $team->getRating()/count($team1);
            $team1Average[1] += $team->getRatingDeviation()/count($team1);
        }
       $team2Average = [0,1];
        foreach ($team1 as $team){
            $team2Average[0] += $team->getRating()/count($team1);
            $team2Average[1] += $team->getRatingDeviation()/count($team1);
        }
        $glicko = new self;

        $team1Result = array_map(function ($team) use ($team2Average, $int1, $glicko) {
            return $glicko->calculateRating($team, [new Result(new Rating(...$team2Average), $int1)]);
        },$team1);
        $team2Result = array_map(function ($team) use ($team1Average, $int2, $glicko) {
            return $glicko->calculateRating($team, [new Result(new Rating(...$team1Average), $int2)]);
        },$team2);
        return [$team1Result,$team2Result];
    }

    public static function compositeTeam($team1, $team2, $int1, $int2)
    {
        //WIP
    }
    public static function individual($team1, $team2, $int1, $int2)
    {
        //WIP
    }

}
