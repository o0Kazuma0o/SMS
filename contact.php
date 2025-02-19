<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Home | Bestlink College of the Philippines</title>
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <section class="sub-header">
      <nav>
        <a href="index.php"> <img src="image/logo.png" /> </a>
        <div class="nav-links">
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="course.php">Course</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="login.php">Login</a></li>
          </ul>
        </div>
      </nav>

      <h1>Contact Us</h1>
    </section>

    <!--  ,...............contact us.......-->

    <section class="location">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d123504.11642641015!2d120.97978729707378!3d14.684087285340135!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397ba0942ef7375%3A0x4a9a32d9fe083d40!2sQuezon%20City%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1739443190714!5m2!1sen!2sph"
        width="600"
        height="450"
        style="border: 0"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </section>

    <section class="contact-us">
      <div class="row">
        <div class="contact-col">
          <i class="fa fa-home"></i>
          <span>
            <h5>Millinaire's Village Campus</h5>
            <h5>Main Campus</h5>
            <h5>Bulacan Campus</h5>
            <p>Quezon City</p>
          </span>
        </div>

        <div class="contact-col">
          <i class="fa fa-envelope-o"></i>
          <span>
            <h5>bcp-inquiry@bcp.edu.ph</h5>
            <p>Email us your query</p>
          </span>
        </div>

        <div class="contact-col">
          <i class="fa fa-phone"></i>
          <span>
            <h5>417-4355</h5>
            <p>Monday to Saturday 10am - 6pm</p>
          </span>
        </div>
      </div>

        <div class="contact-col">
          <form action="form-handler.php" method="post">
            <input
              type="text"
              name="name"
              placeholder="Enter Your Name"
              required
            />
            <input
              type="text"
              name="email"
              placeholder="Enter Your Email"
              required
            />
            <input
              type="text"
              name="subject"
              placeholder="Enter Your Subject"
              required
            />

            <textarea rows="8" name="message" placeholder="Message"></textarea>
            <button type="submit" class="hero-btn red-btn">Send Message</button>
          </form>
        </div>
    </div>
    </section>

    <!--  ,...............Footer.......-->
    <section class="footer">
      <h4>About us</h4>
      <p>
        Lorem ipsum dolor sit amet consectetur adipisicing elit. Officia,
        repudiandae non similique vitae odit voluptatum molestias temporibus
        optio placeat nemo a culpa tenetur repellat praesentium harum itaque
        eveniet, voluptatibus blanditiis
      </p>

      <div class="icons">
        <i class="fa fa-facebook"> Facebook </i>
        <i class="fa fa-twitter"> Twitter </i>
        <i class="fa fa-instagram"> Instagram </i>
      </div>

      <p>
        Made with Heart <i class="fa fa-heart-o"></i> by Tech Code All Rights
        Reserved
      </p>
    </section>
  </body>
</html>
