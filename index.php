<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<?php

require_once "connexion.php";

// Initialisation du message d'erreur
$message_form = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email=$_POST["email"];
    $pwd=$_POST["pwd"];
        
    // Vérifier si l'email et le mot de passe sont valides
    $req = "SELECT * FROM users WHERE email = '$email' AND mdp = '$pwd'";
    $result = $conn->query($req);
    if ($result->num_rows == 1) {
        // L'email et le mot de passe sont valides, rediriger vers la page d'accueil
        header("Location: etudiant.php");
        exit();
    } else {
        // L'email et le mot de passe sont invalides, afficher un message d'erreur
        $message_form= "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centre a Bridge</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- Include WOW.js for animation triggers -->
<script src="https://cdn.jsdelivr.net/npm/wow.js@1.1.2/dist/wow.min.js"></script>
<link rel="stylesheet" href="./css/index.css">
<script>
    new WOW().init();
</script>
<script src="https://cdn.jsdelivr.net/npm/emailjs-com@3.2.0/dist/email.min.js"></script>
<script>
    (function(){
        emailjs.init("7oME-_54-KfY2KO3V");
    })();

    function sendEmail(event) {
        event.preventDefault();

        // Désactiver le bouton pour éviter les envois multiples
        const submitButton = document.getElementById("submitButton");
        submitButton.disabled = true;
        submitButton.innerHTML = "Envoi en cours...";

        // Récupération des données du formulaire
        const fullName = document.getElementById("fullName").value;
        const subject = document.getElementById("subject").value;
        const message = document.getElementById("message").value;

      const email = document.getElementById("email").value;

const templateParams = {
    from_name: fullName,
    to_name: "Centre Alpha Bridge",
    subject: subject,
    message: message,
    from_email: email, // Ajoutez cette ligne
};

        emailjs.send("service_n4l7dpm", "template_ur1khqs", templateParams)
            .then(function(response) {
                console.log('SUCCESS!', response);
                alert("Message envoyé avec succès!");
                submitButton.disabled = false;
                submitButton.innerHTML = "Envoyer <i class='fa-solid fa-arrow-right'></i>";
            })
            .catch(function(error) {
                console.log('FAILED...', error);
                alert("Erreur lors de l'envoi du message. Essayez à nouveau.");
                submitButton.disabled = false;
                submitButton.innerHTML = "Envoyer <i class='fa-solid fa-arrow-right'></i>";
            });
    }
</script>
</head>
<body>

    <nav class="navbar navbar-expand-lg shadow sticky-top" >
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <!-- Logo à gauche -->
            <a href="index.html" class="navbar-brand d-flex align-items-center">
                <img src="./img/logo.png" alt="logo">
                <h5 class="m-0" >Centre Alpha Bridge</h5>
            </a>
            
            <!-- Bouton de menu pour les petits écrans -->
            <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Contenu de la barre de navigation -->
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <!-- Liens de menu au centre -->
                <div class="navbar-nav mx-auto">
                    <a href="" class="nav-item nav-link "><i class="fa-solid fa-house"></i> Home</a>
                    <a href="#about" onclick="about()" class="nav-item nav-link "><i class="fa-solid fa-address-card"></i> About</a>
                    <a href="#Testimonial" onclick="Testimonial()" class="nav-item nav-link "><i class="fa-solid fa-users"></i> Testimonial</a>
                    <a href="#Contactez-Nous" class="nav-item nav-link "><i class="fa-solid fa-envelope"></i> Contact</a>
                </div>
            </div>

            <!-- Bouton Login aligné à droite -->
            <button href="#"  onclick="showForm()"  class="login login-btn ms-3"><i class="fa-solid fa-user"></i> Login</button>
        </div>
    </nav>
    <div class="image-container" id="img_cont" style="background-image: url('./img/img.jpg'); background-size: cover; background-position: center; position: relative;">
    <!-- Texte superposé avec bouton -->
    <div class="text-on-image" id="login" >
        <div id="text" >
            <p id="site" style="font-weight: bold;">
                <b style=" font-size: x-large;"> Bienvenue sur le site officiel d'Alpha Bridge</b>.
            </p>
            <button href="#" class="login-btn" onclick="showForm()" id="cnx">Connexion</button>
        </div>
        
        <!-- Formulaire de connexion -->
        <form id="myForm" method="POST" action="" style="display: none; margin-top: 20px;">
            <div id="error-message"><?php echo isset($message_form) ? $message_form : ''; ?></div>  <br/>

            <label for="email">Your Email:</label><br/>
            <input type="email" id="email" name="email" placeholder="Your Email" required><br/>

            <label for="pwd">Password:</label><br/>
            <input type="password" id="pwd" name="pwd" placeholder="Your Password" required><br>

            <button type="submit" class="login-btn">Se Connecter</button>
        </form>
    </div>
</div>

    
<!--style="position: absolute;top: 62%;"-->
<div class="container" >
        <div class="container" >
            <div class="row g-4">
                <div class="col-lg-3 col-sm-6 wow fadeInUp " data-wow-delay="0.1s">
                    <div class="service-item text-center pt-3" >
                        <div class="p-4 " >
                            <i class="fas fa-3x fa-book-open  mb-4"></i>
                            <h5 class="mb-3">École Primaire</h5>
                            <p> Soutien personnalisé pour favoriser l'apprentissage des bases essentielles</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="service-item text-center pt-3" >
                        <div class="p-4 " >
                            <i class="fas fa-3x fa-chalkboard-teacher  mb-4" ></i>
                            <h5 class="mb-3">Collège</h5>
                            <p> Accompagnement dans le développement des compétences académiques et personnelles.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="service-item text-center  pt-3">
                        <div class="p-4 ">
                            <i class="fa fa-3x fa-graduation-cap  mb-4"></i>

                            <h5 class="mb-3 ">Lycée </h5>
                            <p> Préparation approfondie pour les examens et l'orientation vers l'enseignement supérieur</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="service-item text-center   pt-3" >
                        <div class="p-4 ">
                            <i class="fas fa-3x fa-user-graduate  mb-4" ></i>

                            <h5 class="mb-3 " >Université </h5>
                            <p > Conseils et soutien pour réussir dans les études supérieures et les projets professionnels.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<div class="container-xxl py-5 " id="about" style="display: none;">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                    <div class="position-relative h-100">
                    <img class="img-fluid position-absolute w-100 h-100" src="img/about.jpg" alt="" style="object-fit: cover; border-radius: 10px;">
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInUp"  id="apropos">
                    <h1 class="section-title  text-start  pe-3" >À Propos D'Alpha Bridge</h1>
                    <h6 class="mb-3" style="color: 133E87;">Bienvenue au Centre Alpha Bridge</h6>
                    <p class="mb-4">Alpha Bridge est un centre d'apprentissage dédié à l'épanouissement personnel. Nous offrons des programmes innovants, des ateliers et un accompagnement expert pour aider chacun à atteindre ses objectifs.</p>
                    <p class="mb-4"> Rejoignez-nous pour développer votre potentiel dans un environnement inclusif et motivant.</p>
                    <div class="row gy-2 gx-4 mb-4">
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Experts Pédagogiques</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Formation à Distance</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Certification </p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Cours en Groupe Dynamique</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Préparation aux Examens</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Accompagnement Personnalisé </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Testimonial Start -->
<div class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s" id="Testimonial" >
    <div class="container">
        <div class="text-center">
            <h1 class="section-title  text-center px-3" >Testimonial</h1>
            <h6 class="mb-3" >Ce Que Disent Nos Étudiants !</h6>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-item text-center card animated-card" style=" border: 1px solid white; border-radius: 10px;">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="img/team3.jpg"  style="width:200px;height:200px;">
                    <h5 class="mb-0">Mme Amina</h5>
                    <div class="testimonial-text  text-center p-4" >               
                        <p class="mb-0">Bonne expérience pour mes 2 enfants pour les cours du lycée. Bon niveau de prof de Mathématiques ET PROF TAOUFIK de pc. Je recommande vivement.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-item text-center card animated-card" style=" border: 1px solid white; border-radius: 10px;">
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="img/team1.jpg" style="width:200px;height:200px;" >
                    <h5 class="mb-0">M. Ahmed</h5>
                    <div class="testimonial-text  text-center p-4"  >
                        <p class="mb-0">Grâce à Centre  BRIDGE, mon fils a progressé rapidement en Maths et a obtenu de très bonnes notes à ses examens.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="testimonial-item text-center card animated-card"style=" border: 1px solid white; border-radius: 10px;" >
                    <img class="border rounded-circle p-2 mx-auto mb-3" src="img/team2.jpg" style="width:200px;height:200px;" >
                    <h5 class="mb-0">Mlle Karima</h5>
                    <div class="testimonial-text  text-center p-4" >
                        <p class="mb-0">Les cours particuliers de Centre  BRIDGE m'ont permis de combler mes lacunes en SVT et d'obtenir mon bac avec mention.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Testimonial End -->
  


<!-- Contact Form Start -->
<div class="container my-5" id="Contactez-Nous">
    <h1 class="text-center mb-4" style="color:#FC4100;">Contactez-Nous</h1>
    <div class="row align-items-center">
        <!-- Contact Form -->
        <div class="col-lg-6 col-md-12 wow fadeInLeft" data-wow-delay="0.1s">
            <form class="contact-form p-4 rounded shadow" onsubmit="sendEmail(event)">
                <div class="mb-3">
                    <label for="fullName" class="form-label" style="font-weight:bold;">Nom Complet : 
                        <span style="color:red;">*</span>
                    </label>
                    <input type="text" class="form-control" id="fullName" placeholder="Votre nom complet" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label" style="font-weight:bold;">Email : <span style="color:red;">*</span></label>
                    <input type="email" class="form-control" id="email" placeholder="Votre email" required>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label" style="font-weight:bold;">Objet : 
                        <span style="color:red;">*</span>
                    </label>
                    <input type="text" class="form-control" id="subject" placeholder="Objet de votre message" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label" style="font-weight:bold;">Message : 
                        <span style="color:red;">*</span>
                    </label>
                    <textarea class="form-control" id="message" rows="4" placeholder="Votre message" required></textarea>
                </div>
                
                <button type="submit" id="submitButton" class="login-btn">
                    Envoyer <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <!-- Image à droite -->
        <div class="col-lg-6 col-md-12 d-flex justify-content-center wow fadeInRight" data-wow-delay="0.3s">
            <img src="./img/bg-contact.jpg" alt="Contact Us Image" class="img-fluid rounded shadow animate-img">
        </div>
    </div>
</div>
<!-- Contact Form End -->


    <!--footer-->
    
<footer class="  py-4"  >
    <div class="container">
        <div class="row">
            <!-- Informations principales -->
            <div class="col-12 col-md-3 mb-3 mb-md-0">
                <h5   class="py-3" >Centre Alpha Bridge</h5>
                <p class="text-justify" >
                    Le Centre Alpha Bridge est dédié à fournir des services éducatifs de qualité et à accompagner les étudiants dans leur réussite. Rejoignez-nous pour un apprentissage exceptionnel.
                </p>
            </div>

            <!-- Liens de Navigation -->
            <div class="col-12 col-md-3 mb-3 mb-md-0">
                <h5 class="py-3" >Navigation</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="" style="text-decoration:none;">Accueil</a></li>
                    <li><a href="#" class="" style="text-decoration:none;">À propos</a></li>
                    <li><a href="#about"class="" style="text-decoration:none;" >Nos services</a></li>
                    <li><a href="#Contactez-Nous" class="" style="text-decoration:none;">Contact</a></li>
                </ul>
            </div>

            <!-- Coordonnées -->
            <div class="col-12 col-md-3 mb-3 mb-md-0">
                <h5 class="py-3" >Contact</h5>
                <p>
                <i class="fa-solid fa-location-dot"></i> Quartier industriel n 97 syba - Marrakech<br>
                     <i class="fa-solid fa-phone"></i>  +212 715 595 910<br>
                     <i class="fa-solid fa-envelope"></i> centrealphabridge@gmail.com
                </p>
            </div>

            <!-- Carte Google Maps -->
            <div class="col-12 col-md-3">
                <h5 class="py-4" >Nous Trouver</h5>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.8354345093747!2d144.95373631561698!3d-37.81627977975195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad642af0f11fd81%3A0xf5772d1ff0c5c0a!2sCentre%20Alpha%20Bridge!5e0!3m2!1sen!2sfr!4v1698483142180!5m2!1sen!2sfr" 
                        width="100%" 
                        height="200" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Droits d'auteur -->
        <div class="text-center  mt-3" >
            &copy; 2024 Centre Alpha Bridge - Tous droits réservés
        </div>
    </div>
</footer>





    

    <!-- Bootstrap et FontAwesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function showForm() {
            document.getElementById("myForm").style.display = "block";
            document.getElementById("text").style.display = "none";      
          }
        
function about() {
    // Sélectionner l'élément avec l'ID 'about'
    var aboutElement = document.getElementById('about');
    
    // Alterner l'affichage entre 'block' et 'none'
    if (aboutElement.style.display === 'none' || aboutElement.style.display === '') {
        aboutElement.style.display = 'block';
    } else {
        aboutElement.style.display = 'none';
    }
}
function Testimonial() {
    // Sélectionner l'élément avec l'ID 'about'
    var aboutElement = document.getElementById('Testimonial');
    
    // Alterner l'affichage entre 'block' et 'none'
    if (aboutElement.style.display === 'none' || aboutElement.style.display === '') {
        aboutElement.style.display = 'block';
    } else {
        aboutElement.style.display = 'none';
    }
}

    </script>
</body>
</html>








