<?php

class Player
{
    private $diceInCup = [];
    private $name;
    private $position;
    private $point;

    public function getDiceInCup()
    {
        return $this->diceInCup;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function __construct($numberOfDice, $position, $name = '')
    {
        $this->point = 0;
        $this->position = $position;
        $this->name = $name;

        for ($i = 0; $i < $numberOfDice; $i++) {
            array_push($this->diceInCup, new Dice());
        }
    }
    
    
    public function addPoint($point)
    {
        $this->point += $point;
    }

    
    public function getPoint()
    {
        return $this->point;
    }

    public function play()
    {
        foreach($this->diceInCup as $dice){
            $dice->roll();
        }
    }

    public function removeDice($key)
    {
        unset($this->diceInCup[$key]);
    }

    public function insertDice($dice)
    {
        array_push($this->diceInCup, $dice);
    }
}

class Dice 
{
    private $topSideVal;

    public function getTopSideVal()
    {
        return $this->topSideVal;
    }

    public function roll()
    {
        $this->topSideVal =  rand(1,6);
        return $this;
    }

    public function setTopSideVal($topSideVal)
    {
        $this->topSideVal = $topSideVal;
        return $this;
    }
}

class Game
{
    private $players = [];
    private $round;
    private $numberOfPlayer;
    private $numberOfDicePerPlayer;

    public function __construct($numberOfPlayer, $numberOfDicePerPlayer)
    {
        $this->round = 0;
        $this->numberOfPlayer = $numberOfPlayer;
        $this->numberOfDicePerPlayer = $numberOfDicePerPlayer;

        for ($i = 0; $i < $this->numberOfPlayer; $i++) {
            $this->players[$i] = new Player($this->numberOfDicePerPlayer, $i, $i + 1);
        }
    }

    private function displayRound()
    {
        echo "<strong>Turn {$this->round}</strong><br/>\r\n";
        return $this;
    }

    private function displayTopSideDice($title = 'Roll the dice')
    {
        echo "<span><u>{$title} :</u></span><br/>";
        foreach ($this->players as $player) {
            echo "Player #{$player->getName()} ({$player->getPoint()}) : ";
            $diceTopSide = '';

            foreach ($player->getDiceInCup() as $dice) {
                $diceTopSide .= $dice->getTopSideVal() . ", ";
            }

            if(!$diceTopSide) {
                $diceTopSide = '_ (Stop playing because it has no dice)';
            }

            echo rtrim($diceTopSide, ', ') . "<br/>\r\n";
        }

        echo "<br/>\r\n";
        return $this;
    }

    public function lastPlayerHasDice()
    {
        foreach ($this->players as $player) {
            if(sizeof($player->getDiceInCup())){
                echo "Game ends because only player <strong>#{$player->getName()}</strong> has dice. <br/>\r\n";
                return $this;
            }
        }
    }

    public function displayWinner($player)
    {
        echo "Game won by player <strong>#{$player->getName()}</strong> because it has more points than other players. <br/>\r\n";
        return $this;
    }

    public function start()
    {
        echo "Player = {$this->numberOfPlayer}, Dice = {$this->numberOfDicePerPlayer}<br/><br/>\r\n";

        while (true) {
            $this->round++;
            $diceCarryForward = [];

            foreach ($this->players as $player) {
                $player->play();
            }

            $this->displayRound()->displayTopSideDice();

            foreach ($this->players as $index => $player) {
                $tempDiceArray = [];

                foreach ($player->getDiceInCup() as $diceIndex => $dice) {

                    if ($dice->getTopSideVal() == 6) {
                        $player->addPoint(1);
                        $player->removeDice($diceIndex);
                    }


                    if ($dice->getTopSideVal() == 1) {
                        
                        if ($player->getPosition() == ($this->numberOfPlayer - 1)) {
                            $this->players[0]->insertDice($dice);
                            $player->removeDice($diceIndex);
                        } else {
                            array_push($tempDiceArray, $dice);
                            $player->removeDice($diceIndex);
                        }
                    }
                }

                $diceCarryForward[$index + 1] = $tempDiceArray;

                if (array_key_exists($index, $diceCarryForward) && count($diceCarryForward[$index]) > 0) {

                    foreach ($diceCarryForward[$index] as $dice) {
                        $player->insertDice($dice);
                    }

                    $diceCarryForward = [];
                }
            }

            $this->displayTopSideDice("After Evaluation");

            $playerHasDice = $this->numberOfPlayer;

            foreach ($this->players as $player) {
                if (count($player->getDiceInCup()) <= 0) {
                    $playerHasDice--;
                }
            }

            if ($playerHasDice == 1) {
                $this->lastPlayerHasDice();
                $this->displayWinner($this->getWinner());
                break;
            }
        }
    }

    private function getWinner()
    {
        $winner = null;
        $highscore = 0;
        foreach ($this->players as $player) {
            if ($player->getPoint() > $highscore) {
                $highscore = $player->getPoint();
                $winner = $player;
            }
        }

        return $winner;
    }
}

$game = new Game(3, 4);
$game->start();