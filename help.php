<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 2/1/2018
 * Time: 12:56 AM
 */
session_start();

include "connect.php";
include "inc/login_checker.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BitcoinPVP - Help</title>
    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>

    <!-- Custom scripts -->

    <!-- Custom style -->
    <link href="css/style.css" rel="stylesheet">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<header>
    <?php include 'inc/header.php'; ?>
</header>
<main class="valign-wrapper">
    <div class="container">
        <div class="row top-buffer-30">
            <div class="col s12">
                <div class="row">
                    <div class="col l5 m7 s12">
                        <div class="card blue lighten-5">
                            <div class="card-content">
                                <span class="card-title"><b>Content</b></span>
                                <a href="#what-is">What is BitcoinPVP?</a><br>
                                <a href="#how-to">How to play?</a><br>
                                <a href="#what-are">What are bits?</a><br>
                                <a href="#is-there">Is there any fee for playing?</a><br>
                                <a href="#how-do">How do I deposit bits?</a><br>
                                <a href="#how-long">How long do deposits take to be credited?</a><br>
                                <a href="#how-can">How can I contact the support team?</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row"></div>
                <div class="row"></div>
                <div class="row" id="what-is"><h5>What is BitcoinPVP?</h5>
                    <p> BitcoinPVP is a bitcoin gambling game. It is a secure, real time game where you can play for fun
                        or
                        to
                        earn your living.</p>
                </div>
                <div class="row" id="how-to">
                    <h5>How to play?</h5>
                    <p> Each round, you have the opportunity to buy a number. You can buy up to 30 numbers per play, you
                        can
                        play limitless times each round. Each number costs 100 bits. Each round lasts a minute. After
                        each
                        round
                        the winner will be selected based on the frequency of the numbers that the players
                        bought.<br><br>
                        The least frequent frequency of a number will be selected as long as the frequency is below 30.
                        If
                        two
                        frequencies are the same, the lowest number will be selected. This means that there can be at
                        the
                        most
                        30 winners per round.<br><br>
                        <b>Examples:</b>
                    <p>The frequency column represents how many times a number was bought. The frequency of number's
                        frequency
                        column represents how many times a frequency value repeats. This column determines the winner
                        number.
                        The lowest value in the frequency of frequency column whose frequency value is below 30 and has
                        the
                        lowest number determines the winner number.</p>
                    <div class="row">
                        <div class="legend red"></div>
                        <span><b>Not below 30</b></span><br>
                        <div class="legend amber"></div>
                        <span><b>Below 30</b></span><br>
                        <div class="legend green"></div>
                        <span><b>Lowest frequency of frequency's number and lowest number</b></span>
                    </div>
                    <div class="row">
                        <div class="col l4 m6 s12">
                            <table class="centered highlight">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Frequency</th>
                                    <th>Frequency of number's frequency</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="lose red-text">
                                    <td>1</td>
                                    <td>500</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>2</td>
                                    <td>310</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>3</td>
                                    <td>100</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>4</td>
                                    <td>80</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>5</td>
                                    <td>75</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>6</td>
                                    <td>50</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>7</td>
                                    <td>31</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td colspan="3">...</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>31</td>
                                    <td>4</td>
                                    <td>2</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>32</td>
                                    <td>3</td>
                                    <td>2</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>33</td>
                                    <td>3</td>
                                    <td>2</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>34</td>
                                    <td>4</td>
                                    <td>2</td>
                                </tr>
                                <tr class="win green-text">
                                    <td>35</td>
                                    <td>15</td>
                                    <td>1</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col l4 m6 s12">
                            <table class="centered highlight">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Frequency</th>
                                    <th>Frequency of number's frequency</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="lose red-text">
                                    <td>1</td>
                                    <td>54</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>2</td>
                                    <td>55</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>3</td>
                                    <td>80</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>4</td>
                                    <td>150</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>5</td>
                                    <td>91</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>6</td>
                                    <td>72</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>7</td>
                                    <td>38</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td colspan="3">...</td>
                                </tr>
                                <tr class="win green-text">
                                    <td>54</td>
                                    <td>3</td>
                                    <td>2</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>55</td>
                                    <td>3</td>
                                    <td>2</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>65</td>
                                    <td>8</td>
                                    <td>3</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>72</td>
                                    <td>8</td>
                                    <td>3</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>80</td>
                                    <td>8</td>
                                    <td>3</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col l4 m6 offset-m3 s12">
                            <table class="centered highlight">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Frequency</th>
                                    <th>Frequency of number's frequency</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="lose red-text">
                                    <td>1</td>
                                    <td>500</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>2</td>
                                    <td>310</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>3</td>
                                    <td>100</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>4</td>
                                    <td>80</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>5</td>
                                    <td>75</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>6</td>
                                    <td>50</td>
                                    <td>1</td>
                                </tr>
                                <tr class="lose red-text">
                                    <td>7</td>
                                    <td>31</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td colspan="3">...</td>
                                </tr>
                                <tr class="win green-text">
                                    <td>31</td>
                                    <td>7</td>
                                    <td>5</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>32</td>
                                    <td>7</td>
                                    <td>5</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>33</td>
                                    <td>7</td>
                                    <td>5</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>34</td>
                                    <td>7</td>
                                    <td>5</td>
                                </tr>
                                <tr class="close amber-text">
                                    <td>35</td>
                                    <td>7</td>
                                    <td>5</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row" id="what-are">
                    <h5>What are bits?</h5>
                    <p>A bit is a millionth of a bitcoin. It is the digital currency we use to play this game. You can
                        see
                        the
                        exchange rate bit/USD on this website <a href="https://bitsusd.com/">
                            bitsusd.com</a></p>
                </div>
                <div class="row" id="is-there">
                    <h5>Is there any fee for playing?</h5>
                    <p>We take 5% of the cost of each number to pay the mining fees of the microtransactions, and to
                        keep
                        our
                        servers running.</p>
                </div>
                <div class="row" id="how-do">
                    <h5>How do I deposit bits?</h5>
                    <p>Each user account has a bitcoin wallet address associated with it. You must deposit your bits to
                        this
                        address. You can find your bitcoin wallet address in your account management page.</p>
                    <a href="account.php">Go to your account</a>
                </div>
                <div class="row" id="how-long">
                    <h5>How long do deposits take to be credited?</h5>
                    <p>As soon the bitcoin transaction is confirmed, your account will be credited. The lower the fee
                        you
                        pay for
                        your transaction the longer it will take to confirm. It is up to you to decide the fee you will
                        include in
                        your transaction.</p>
                </div>
                <div class="row" id="how-can">
                    <h5>How can I contact the support team?</h5>
                    <p>For the moment, only registered users can contact the support team. You will have to go to the
                        account
                        management page to send a ticket.</p>
                    <a href="account.php">Go to your account</a>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'inc/footer.php' ?>
</body>
</html>

