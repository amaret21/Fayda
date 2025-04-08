<?php
session_start(); // Start a session to store the selected language

if (isset($_POST['language'])) {
    $_SESSION['language'] = $_POST['language'];
}

$current_language = $_SESSION['language'] ?? 'en'; // Default to English
?>
<header>
    <table class="table-style" width="97%" border="0" cellspacing="0" cellpadding="0">
        <thead>
            <tbody>
                <tr height="8">
                    <td colspan="0">
                        <marquee>
                            <?php
                            if ($current_language === 'en') echo "Welcome to Fayda SACCOs! Together to The Future!";
                            elseif ($current_language === 'am') echo "ወደ ፋይዳ SACCOs እንኳን በደህና መጡ! ወደፊት በአንድነት!";
                            elseif ($current_language === 'om') echo "Baga gara Fayda SACCOs dhuftan! Gara Fuulduraatti Waliin!";
                            ?>
                        </marquee>
                    </td>
                    <td align="right" width="9%">
                        <form method="post">
                            <select id="language" name="language" onchange="this.form.submit()">
                                <option value="en" <?php if ($current_language === 'en') echo 'selected'; ?>>English</option>
                                <option value="am" <?php if ($current_language === 'am') echo 'selected'; ?>>አማርኛ (Amharic)</option>
                                <option value="om" <?php if ($current_language === 'om') echo 'selected'; ?>>Afaan Oromo</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td width="91%" align="right">
                        <div id="datetime"></div>
                    </td>
                    <td width="10%" align="right"><a href="register.php">Register</a><a>/</a><a
                            href="login.php">Login</a></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <nav>
                            <ul>
                                <li align="left" style="font-family:Georgia, 'Times New Roman', Times, serif;"><a
                                        href="index.php"><img src="image/logo.png" alt="Fayda SACCO Logo"
                                            width="80" height="75" style="vertical-align: middle;">
                                        <?php
                                        if ($current_language === 'en') echo "Together to The Future!";
                                        elseif ($current_language === 'am') echo "ወደፊት በአንድነት!";
                                        elseif ($current_language === 'om') echo "Gara Fuulduraatti Waliin!";
                                        ?>
                                    </a></li>
                                <li class="dropdown">
                                    <a href="about.html">
                                        <?php
                                        if ($current_language === 'en') echo "About Us";
                                        elseif ($current_language === 'am') echo "ስለ እኛ";
                                        elseif ($current_language === 'om') echo "Waa'ee Keenyaa";
                                        ?>
                                    </a>
                                    <ul class="submenu">
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Mission, Vision and Objectives";
                                                elseif ($current_language === 'am') echo "ተልዕኮ፣ ራዕይ እና ዓላማዎች";
                                                elseif ($current_language === 'om') echo "Ergama, Mul'ataafi Kaayyoo";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Our Team";
                                                elseif ($current_language === 'am') echo "ቡድናችን";
                                                elseif ($current_language === 'om') echo "Garee Keenya";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Partners";
                                                elseif ($current_language === 'am') echo "አጋሮች";
                                                elseif ($current_language === 'om') echo "Hiriyoota";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Careers";
                                                elseif ($current_language === 'am') echo "ሥራዎች";
                                                elseif ($current_language === 'om') echo "Carraalee Hojii";
                                                ?>
                                            </a></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="services.html">
                                        <?php
                                        if ($current_language === 'en') echo "Services";
                                        elseif ($current_language === 'am') echo "አገልግሎቶች";
                                        elseif ($current_language === 'om') echo "Tajaajiloota";
                                        ?>
                                    </a>
                                    <ul class="submenu">
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Regular Saving";
                                                elseif ($current_language === 'am') echo "መደበኛ ቁጠባ";
                                                elseif ($current_language === 'om') echo "Qusannaa Idilee";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Loan Saving";
                                                elseif ($current_language === 'am') echo "የብድር ቁጠባ";
                                                elseif ($current_language === 'om') echo "Qusannaa Liqii";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Girls Saving Account";
                                                elseif ($current_language === 'am') echo "የሴቶች የቁጠባ ሂሳብ";
                                                elseif ($current_language === 'om') echo "Herrega Qusannaa Shamarranii";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "IF services";
                                                elseif ($current_language === 'am') echo "አይኤፍ አገልግሎቶች";
                                                elseif ($current_language === 'om') echo "Tajaajiloota IF";
                                                ?>
                                            </a></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="media.html">
                                        <?php
                                        if ($current_language === 'en') echo "Media";
                                        elseif ($current_language === 'am') echo "መገናኛ";
                                        elseif ($current_language === 'om') echo "Miidiyaa";
                                        ?>
                                    </a>
                                    <ul class="submenu">
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Testimonials";
                                                elseif ($current_language === 'am') echo "ምስክርነቶች";
                                                elseif ($current_language === 'om') echo "Ragaalee";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Photo Gallery";
                                                elseif ($current_language === 'am') echo "የፎቶ ጋለሪ";
                                                elseif ($current_language === 'om') echo "Gaaleriin Suuraa";
                                                ?>
                                            </a></li>
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Video";
                                                elseif ($current_language === 'am') echo "ቪዲዮ";
                                                elseif ($current_language === 'om') echo "Viidiyoo";
                                                ?>
                                            </a></li>
                                    </ul>
                                </li>
                                <li><a href="vacancy.html">
                                        <?php
                                        if ($current_language === 'en') echo "Vacancy";
                                        elseif ($current_language === 'am') echo "ክፍት ቦታ";
                                        elseif ($current_language === 'om') echo "Bakka Hojii Duwwaa";
                                        ?>
                                    </a></li>
                                <li class="dropdown">
                                    <a href="documents.html">
                                        <?php
                                        if ($current_language === 'en') echo "Documents";
                                        elseif ($current_language === 'am') echo "ሰነዶች";
                                        elseif ($current_language === 'om') echo "Dokumentoota";
                                        ?>
                                    </a>
                                    <ul class="submenu">
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Awards And Certificates";
                                                elseif ($current_language === 'am') echo "ሽልማቶች እና የምስክር ወረቀቶች";
                                                elseif ($current_language === 'om') echo "Badhaasaafi Ragaalee";
                                                ?>
                                            </a></li>
                                    </ul>
                                </li>
                                <li class="dropdown">
                                    <a href="contact.html">
                                        <?php
                                        if ($current_language === 'en') echo "Contact Us";
                                        elseif ($current_language === 'am') echo "ያግኙን";
                                        elseif ($current_language === 'om') echo "Nu qunnamaa";
                                        ?>
                                    </a>
                                    <ul class="submenu">
                                        <li><a href="#">
                                                <?php
                                                if ($current_language === 'en') echo "Offices";
                                                elseif ($current_language === 'am') echo "ቢሮዎች";
                                                elseif ($current_language === 'om') echo "Waajjiraalee";
                                                ?>
                                            </a></li>
                                    </ul>
                                </li>
                                <li><a href="faq.php">FAQs</a></li>
                            </ul>
                        </nav>
                    </td>
                </tr>
            </tbody>
        </thead>
    </table>
</header>