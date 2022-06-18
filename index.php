<?php

$splashScreenPixelRows = file("splashImage.txt");

function drawSplashScreen($splashScreenPixelRows): void
{
    for ($i = 0; $i < count($splashScreenPixelRows); $i++){

        $textArr = substr($splashScreenPixelRows[$i],0, -1);
        $splashScreenPixelRows[$i] =
            array_merge( array_fill(0, leftPadding," "), str_split($textArr));
    }
    $frameCounter = 1;

    while ($frameCounter < count($splashScreenPixelRows[0]) - 1){
        usleep(10000);
        system("clear");
        foreach ($splashScreenPixelRows as $pixelRow){

            $stringToDraw = implode("",
                    array_slice($pixelRow,count($splashScreenPixelRows[0]) - 1 - $frameCounter)) . PHP_EOL;
            echo $stringToDraw;
        }
        $frameCounter++;
    }
     sleep(3);
}

$gamePixelRows = array_fill(0,26,array_fill(0,100," "));

//game logic
$balance = 0.0;
$creditsWon = 0;
$bet = 0;
const specialFruits = [4];
const fruitsTypes = [
    [
        " **** ",
        "*    *",
        "*    *",
        " **** "
    ],
    [
        "  ++  ",
        "++++++",
        "  ++  ",
        "  ++  "
    ],
    [
        "X    X",
        " X  X ",
        " X  X ",
        "X    X"
    ],
    [
        "BBBBBB",
        "OOOOOO",
        "OOOOOO",
        "KKKKKK"
    ],
    [
        "  69  ",
        " 6969 ",
        "696969",
        "  69  "
    ],
    [
        "||||||",
        "||||  ",
        "||    ",
        "|     "
    ],
    [
        "  AA  ",
        " A  A ",
        "A AA A",
        "A    A"
    ],
    [
        "   11 ",
        "  111 ",
        "   11 ",
        "   11  "
    ],
    [
        " 2222 ",
        "    22",
        "  22  ",
        "22 2 2"
    ],
    [
        "333333",
        "  33  ",
        "   333",
        "33333 "
    ],
    [
        "H    H",
        "HHHHHH",
        "H    H",
        "H    H"
    ],
    [
        " 0000 ",
        "0 0  0",
        "0  0 0",
        " 0000 "
    ],
//    [
//        "IIIIII",
//        "  II  ",
//        "  II  ",
//        "IIIIII"
//    ]
];
const leftPadding = 10;
const seedRoof = 30;
$gameMatrix = [
    [1,1,1,1,1],
    [2,2,2,2,2],
    [3,3,3,3,3]
];

const comboLines = [
    [[1,0],[1,1],[1,2],[1,3],[1,4]],
    [[0,0],[0,1],[0,2],[0,3],[0,4]],
    [[2,0],[2,1],[2,2],[2,3],[2,4]],
    [[0,0],[1,1],[2,2],[1,3],[0,4]],
    [[2,0],[1,1],[0,2],[1,3],[2,4]],
    [[1,0],[2,1],[2,2],[2,3],[1,4]],
    [[1,0],[0,1],[0,2],[0,3],[1,4]],
    [[2,0],[2,1],[1,2],[0,3],[0,4]],
    [[0,0],[0,1],[1,2],[2,3],[2,4]],
    [[2,0],[1,1],[1,2],[1,3],[0,4]]
];


function shuffleGameMatrix(array $gameMatrix, array $gamePixelRows): array{
    $randomSeeds = array_fill(0,count($gameMatrix[0]),0);
    $randomSeeds = array_map(fn() => rand(0,seedRoof),$randomSeeds);
    for ($i = 0; $i < count($randomSeeds); $i++){

        for ($j = 0; $j < $randomSeeds[$i]; $j++){
            //todo animation


            for ($k = 0; $k < count($gameMatrix); $k++){
                if($gameMatrix[$k][$i] == count(fruitsTypes) - 1){
                    $gameMatrix[$k][$i] = 0;
                }else{
                    $gameMatrix[$k][$i]++;
                }

            }
        }

    }
    return $gameMatrix;
}

function getWinnings(int $bet, array $shuffledMatrix, int $lineCount): int {
    $winnings = 0;
    $lineCount = count(comboLines) - (count(comboLines) - $lineCount);




    for ($i = 0; $i < $lineCount; $i++){
        $fruitsInLine = [];
        $equalFruitsInLine = [];
        $specialFruitCount = 0;
        $mainFruitCount = 0;
        for ($j = 0; $j < count(comboLines[0]); $j++){
            $fruitsInLine[] = $shuffledMatrix[comboLines[$i][$j][0]][comboLines[$i][$j][1]];
        }
        $mainFruit = -1;
        for ($j = 0; $j < count($fruitsInLine); $j++){
            if ($mainFruit == -1 && !in_array($fruitsInLine[$j], specialFruits)) {
                $mainFruit = $fruitsInLine[$j];
                $equalFruitsInLine[] = $fruitsInLine[$j];
            }else if($mainFruit == $fruitsInLine[$j] || in_array($fruitsInLine[$j], specialFruits)){
                $equalFruitsInLine[] = $fruitsInLine[$j];
            }else{
                break;
            }

        }
        foreach ($equalFruitsInLine as $fruit){
            if(in_array($fruit,specialFruits)){
                $specialFruitCount++;
                continue;
            }
                $mainFruitCount++;
        }
        switch (true){
            case $mainFruitCount == 3:
                $winnings += $bet * 0.5;
                break;
            case $mainFruitCount == 4:
                $winnings += $bet * 2.5;
                break;
            case $specialFruitCount == 3:
            case $mainFruitCount == 5:
                $winnings += $bet * 10;
                break;
            case $specialFruitCount == 2:
                $winnings += $bet;
                break;
            case $specialFruitCount == 4:
                $winnings += $bet * 100;
                break;
            case $specialFruitCount == 5:
                $winnings += $bet * 500;
                break;
        }

    }


    return $winnings;
}

function drawGameMatrix(array $gameMatrix ,array $GamePixelRows): array{

    $jSeparator = intdiv(count($GamePixelRows[0])-40,5) ;
    $iSeparator = intdiv(count($GamePixelRows)-7,3);

    $gameMatrixRowCounter = 0;
    for ($i = 1; $i < (count($GamePixelRows) - 7); $i = $iSeparator + $i ){

        for ($l = 0; $l < 4; $l++){
            $gameMatrixColumnCounter = 0;
            for ($j = 24; $j < count($GamePixelRows[0])-20; $j = $j + $jSeparator) {
                $fruit = fruitsTypes[$gameMatrix[$gameMatrixRowCounter][$gameMatrixColumnCounter]];
                for ($k = 0; $k < strlen($fruit[0]); $k++) {
                    $GamePixelRows[$i+$l][$j + $k] = $fruit[$l][$k];
                }
                $gameMatrixColumnCounter++;
            }
        }
        $gameMatrixRowCounter++;
    }
    return $GamePixelRows;
}

function drawInfo(array $GamePixelRows, int $balance, int $creditsWon, int $bet): array
{

    $balanceText = "Balance = " . $balance;
    $creditsText = "Won = " . $creditsWon;
    $betText = "Your bet = " . $bet;
    for ($i = 1; $i < count($GamePixelRows[0])-2; $i++){
        $GamePixelRows[count($GamePixelRows)-5][$i] = " ";
    }
    for ($i = 0; $i < strlen($balanceText); $i++){
        $GamePixelRows[count($GamePixelRows)-5][$i + 10] = str_split($balanceText)[$i];
    }
    if($creditsWon != 0){
        for ($i = 0; $i < strlen($creditsText); $i++){
            $GamePixelRows[count($GamePixelRows)-5][$i + 45] = $creditsText[$i];
        }
    }

    for ($i = 0; $i < strlen($betText); $i++){
        $GamePixelRows[count($GamePixelRows)-5][$i + 75] = $betText[$i];
    }
    return $GamePixelRows;
}


function drawFrame(array $GamePixelRows): array{
    $rowSplittersPos = [0,17,(count($GamePixelRows) - 1)];
    $columnSplittersPos = [0,20,80,(count($GamePixelRows[0]) - 1)];
    //rows
    for ($i = 0; $i < count($GamePixelRows); $i++){
        //columns
        for ($j = 0; $j < count($GamePixelRows[0]); $j++){
            if( $i != (count($GamePixelRows) - 1) &&
                $i > $rowSplittersPos[1] && $j > 0 &&
                $j < (count($GamePixelRows[0]) - 1) ){
                continue;
            }
            if ((in_array($i, $rowSplittersPos)) || (in_array($j, $columnSplittersPos))){
                $GamePixelRows[$i][$j] = "%";
            }


        }
    }
    return $GamePixelRows;
}

function drawDisplayPixels(array $PixelRows): void
{
    system("clear");
        foreach ($PixelRows as $pixelRow){
            //shifting display to the right
            array_unshift(
                $pixelRow,
                implode("",array_fill(0, leftPadding," ")));
            //drawing display values
            echo implode(
                "",
                    $pixelRow) . PHP_EOL;
        }

    }

drawSplashScreen($splashScreenPixelRows);

$gameMatrix = shuffleGameMatrix($gameMatrix,$gamePixelRows);
$gamePixelRows = drawInfo($gamePixelRows, (string)$balance, (string)$creditsWon, (string)$bet );
$gamePixelRows = drawFrame($gamePixelRows);
$gamePixelRows = drawGameMatrix($gameMatrix, $gamePixelRows);
drawDisplayPixels($gamePixelRows);

$balance = readline(str_repeat(" ", leftPadding)."How much money you wanna lose? (cents) : ");

$gamePixelRows = drawInfo($gamePixelRows, (string)$balance, (string)$creditsWon, (string)$bet );
drawDisplayPixels($gamePixelRows);


while (true) {
    while (true) {
        $start = false;
        echo str_repeat(" ", leftPadding) . " Wanna start? Press \"enter\" \n" .
            str_repeat(" ", leftPadding) . " Wanna run with your money? Type \"R\" (recommended)\n" .
            str_repeat(" ", leftPadding) . " Wanna lose money? Place your bet. (number)\n";
        $input = readline(str_repeat(" ", leftPadding) . " Make your choice wisely: ");
        switch (true) {
            case ($input == "" && $bet > 0):
                $start = true;
                break;
            case $input == "R":
                die;
            case is_numeric($input):
                $bet = (int)$input;
                break;

        }
        $gamePixelRows = drawInfo($gamePixelRows, $balance, $creditsWon, $bet);
        drawDisplayPixels($gamePixelRows);
        if ($start) {
            if($balance - $bet < 0 && $bet <= 10){
                drawDisplayPixels($gamePixelRows);
                echo str_repeat(" ", leftPadding) . "Not enough money! You were warned...". PHP_EOL;
                die;
            }
            $balance = $balance - $bet;
            break;
        }
    }
    $gameMatrix = shuffleGameMatrix($gameMatrix,$gamePixelRows);

    $creditsWon = getWinnings($bet, $gameMatrix, 10);

    $gamePixelRows = drawFrame($gamePixelRows);
    $gamePixelRows = drawGameMatrix($gameMatrix, $gamePixelRows);
    $gamePixelRows = drawInfo($gamePixelRows, $balance, $creditsWon, $bet);
    drawDisplayPixels($gamePixelRows);
    if($creditsWon != 0){
        readline(str_repeat(" ", leftPadding) . "Collect winnings!");
        $balance += $creditsWon;
        $creditsWon = 0;
        $gamePixelRows = drawInfo($gamePixelRows, $balance, $creditsWon, $bet);
        drawDisplayPixels($gamePixelRows);
    }


}








