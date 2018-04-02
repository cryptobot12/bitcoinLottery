<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 2/1/2018
 * Time: 12:56 AM
 */
session_start();

include "globals.php";
include "inc/login_checker.php";

$title = "Help - BitcoinPVP";
include 'inc/header.php'; ?>
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
                                <a href="#how-do-i">How do I transfer bits to other wallets?</a><br>
                                <a href="#how-can">How can I contact the support team?</a><br>
                                <a href="#do-you">Do you have a mobile app for iPhone or Android?</a>
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
                    <p> Each round, you have the opportunity to buy a number. You can buy up to 25 numbers per play, you
                        can play limitless times each round. Each number costs 100 bits. Each round lasts a minute.
                        After each round the winner will be selected based on the frequency of the numbers that the
                        players bought.<br><br>
                        The frequency that repeats the least will be selected as long as the frequency is below 10.
                        If two or more frequencies repeat the same amount of times, the one that contains the lowest
                        number will be selected. This means that there can be at the most 10 winners per round.<br><br>
                        <b>Examples:</b>
                    <p>The frequency column represents how many times a number was bought. The frequency of number's
                        frequency
                        column represents how many times a frequency value repeats. This column determines the winner
                        number.
                        The lowest value in the frequency of frequency column whose frequency value is below 10 and has
                        the
                        lowest number determines the winner number.</p>
                    <div class="row">
                        <div class="legend red"></div>
                        <span><b>f(Frequency) not below 10</b></span><br>
                        <div class="legend amber"></div>
                        <span><b>f(Frequency) below 10</b></span><br>
                        <div class="legend green"></div>
                        <span><b>Lowest f(Frequency) and lowest number</b></span>
                    </div>
                    <div class="row">
                        <div class="col l4 m6 s12">
                            <table class="centered highlight">
                                <thead>
                                <tr>
                                    <th>Number</th>
                                    <th>Frequency</th>
                                    <th>f(Frequency)</th>
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
                                    <td>10</td>
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
                                    <th>f(Frequency)</th>
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
                                    <th>f(Frequency)</th>
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
                    <p>We take 5% of the cost of each number to pay the mining fees of the microtransactions, to
                        keep our servers running, and for our profit.</p>
                </div>
                <div class="row" id="how-do">
                    <h5>How do I deposit bits?</h5>
                    <p>Each user account has a bitcoin wallet address associated with it. You must deposit bitcoin to
                        this
                        address. You can find your bitcoin wallet address in your account management page. If you do not
                        have any bitcoins, we can recommend <a href="https://localbitcoins.com/">LocalBitcoins.com</a> ,
                        which lists traders in your area willing to sell (or buy) bitcoins. You can also search online
                        to see if there are any bitcoin ATMs near you. And remember, you can withdraw directly to your
                        BitcoinPVP bitcoin address.</p>
                    <a href="<?php echo $base_dir;?>account">Go to your account</a>
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
                <div class="row" id="how-do-i">
                    <h5>How do I transfer bits to other wallets?</h5>
                    <p>You can transfer bits to any bitcoin wallet or other BitcoinPVP players through your account management page.</p>
                    <a href="<?php echo $base_dir;?>account">Go to your account</a>
                </div>
                <div class="row" id="how-can">
                    <h5>How can I contact the support team?</h5>
                    <p>You can send an email to <a>support@bitcoinpvp.net</a> or if you are registered you can go to the
                        account
                        management page to send a ticket.</p>
                    <a href="<?php echo $base_dir;?>account">Go to your account</a>
                </div>
                <div class="row" id="do-you">
                    <h5>Do you have a mobile app for iPhone or Android?</h5>
                    <p>We do not have an iOS or Android app.</p>

                    <p>However, our website is fully optimized to run from your mobile browser, so you can fully
                        participate from any device. </p>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Jquery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Compiled and minified JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js"></script>
<script>
    $(document).ready(function () {
        M.AutoInit();
    });
</script>
<?php include 'inc/footer.php' ?>



