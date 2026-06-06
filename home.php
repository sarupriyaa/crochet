<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/crochet/navbar.css">
    <link rel="stylesheet" href="../search/search.css">
    <link rel="stylesheet" href="../crochet/style.css">




    <style>
        a{
            text-decoration:none;
            color:white;
        }
        .hero-section{
    width:90%;
    margin:40px auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:50px;
    min-height:85vh;
}

.hero-left{
    flex:1;
}

.hero-left h1{
    font-size:75px;
    color:#222;
    margin-bottom:15px;
}

.hero-left h1 span{
    color:#e91e63;
}

.hero-left h2{
    font-size:28px;
    color:#8b5e3c;
    margin-bottom:20px;
}

.hero-left p{
    font-size:17px;
    line-height:1.8;
    color:#666;
    max-width:500px;
    margin-bottom:35px;
}

.hero-btn{
    padding:16px 38px;
    border:none;
    border-radius:40px;
    background:linear-gradient(135deg,#8b5e3c,#e91e63);
    color:white;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

.hero-btn:hover{
    transform:translateY(-4px);
    box-shadow:0 12px 25px rgba(233,30,99,0.25);
}

.hero-right{
    flex:1;
    display:flex;
    justify-content:center;
}

.hero-right img{
    width:100%;
    max-width:650px;
    height:85vh;
    object-fit:cover;
}

/* MOBILE */
@media(max-width:900px){

.hero-section{
    flex-direction:column;
    text-align:center;
    padding-top:30px;
}

.hero-left h1{
    font-size:50px;
}

.hero-right img{
    height:60vh;
}

.hero-left p{
    margin:auto;
    margin-bottom:30px;
}

}
    </style>
</head>
<body>
    <?php include "navbar.php"; ?>
<div class="container">
   <div class="hero-section">

    <div class="hero-left">
        <h1>Love <span>Crochet</span></h1>

        <h2>Timeless Handmade Crochet Crafts</h2>

        <p>
            Discover beautiful handmade crochet products crafted with love,
            creativity and elegance for every special moment.
        </p>

        <button class="hero-btn">
            SHOP NOW
        </button>
    </div>

    <div class="hero-right">
        <!-- <img src="https://www.blingcute.com/cdn/shop/files/Untitled_design_e4d12856-487c-4883-89b0-153565d9b32a_1024x1024.jpg?v=1638346240"> -->
         <!-- <img src="https://i.pinimg.com/1200x/85/76/81/857681d5be5af9035204ee42b84b47bc.jpg" alt=""> -->
          <img src="https://i.pinimg.com/1200x/2b/85/a4/2b85a4a74b91cd0b869f59c96ad85fae.jpg" alt="">
    </div>

</div></div>

<div class="container">
    <div class="content">
        <h2 class="head3">Follow us on instagram</h2>
        <p><a href="https://www.instagram.com/blingcutecrochet/">@daisyhook</a></p>
        <div class="hero1">
            <div>
                <img src="https://cdn11.bigcommerce.com/s-tgrcca6nho/images/stencil/original/products/65223/135910/Charming-Crochet-Flowers-Bouquet-JNK-4739_135909__38378.1757829626.jpg" alt="">
            </div>
            <div>
                <img src="https://i.pinimg.com/474x/36/0c/ae/360cae286ce76ce887ba49d6c586694b.jpg" alt="">
            </div>
            <div>
                <img src="https://www.artsty.com/cdn/shop/files/a3_1d349448-f7d4-4174-bc85-ceca78b6a685.jpg?v=1728984419" alt="">
            </div>
            <div>
                <img src="https://hookok.com//wp-content/uploads/2023/09/911flowerbouquets-123-683x1024.jpg" alt="">
            </div>
            <div>
                <img src="https://i5.walmartimages.com/asr/950b18c7-a115-4bb4-a8cc-ce7f55fcd1b5.31566178bd6119f59fb2bf2a6b5599ba.jpeg?odnHeight=768&odnWidth=768&odnBg=FFFFFF" alt="">
            </div>
            <div>
                <img src="https://5.imimg.com/data5/SELLER/Default/2023/9/346871207/FM/UB/BE/102597842/handmade-crochet-flower-bouquet.png" alt="">
            </div>
        </div>
    </div>
</div>

<div class="shop">
    <div class="title1">
        <h3>Shop Our Top Sellers</h3>
        <button class="button2">SHOP NOW</button>
    </div>
    <div class="title2">
        <img src="https://www.blingcute.com/cdn/shop/files/O1CN01a1uZqF1vbz8m6pYvB__2328716192_1024x1024.jpg?v=1638431507" alt="">
    </div>
</div>

<div class="handcraft">
        <h3>Handcrafted with love</h3>
    <div class="crafts">
        <div class="card1">
            <img src="images/sweater.jpg" alt="">
            <h4>Home Decoration</h4>
        </div>
        <div class="card2">
            <img src="images/bag.jpg" alt="">
            <h4>Bags</h4>
        </div>
        <div class="card3">
            <img src="images/hats.jpg" alt="">
            <h4>Hats</h4>
        </div>
        <div class="card4">
            <img src="https://i.pinimg.com/474x/36/0c/ae/360cae286ce76ce887ba49d6c586694b.jpg" alt="">
            <h4>Crochet Bouquets</h4>
        </div>
        <div class="card6">
            <img src="https://chubbiesbyash.com/wp-content/uploads/2024/09/IMG_2862-e1728446125565.jpg" alt="">
            <h4>Crochet Keychain</h4>
        </div>
    </div>
</div>

<div class="team">
    <div class="title3">
      <h3>About Us</h3>
      <p>DaidyHook is a crocheting brand with handcrafted crochet items for sale online. Making beautiful things through our own hands and using 
        handy instruments is a fantastic process that allows you to relax and enjoy yourself while generating new ideas and polishing your work.
      </p>
      <button class="button3"><a href="about.php">Learn more</a></button>
    </div>
</div>

<!-- <div class="highlight">
    <h2>Crochet with Love!</h2>
    <p>100% Handmade Crochet</p>
</div> -->
<?php include "footer.php";?>
<!-- <p>    © 2026 Love Crochet | All Rights Reserved</p> -->
</body>
</html>
<body>


