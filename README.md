<a id="readme-top"></a>

<!-- PROJECT LOGO -->
<div align="center">
   <picture>
      <img src="https://res.cloudinary.com/dshviljjs/image/upload/v1725492558/logo-plain_i8cldo.png" alt="Banner">
   </picture>

<h3 align="center">Atalanta A.C. API</h3>

  <p align="center">
    The powerhouse behind Atalanta A.C., built with Laravel and Filament, handling everything from order management and user handling to secure payments and automated email delivery.
    <br />
    <a href="/"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://www.atalanta.world">View Demo</a>
  </p>
</div>

---

<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#about-the-project">About The Project</a></li>
    <li><a href="#built-with">Built With</a></li>
    <li><a href="#project-overview">Project Overview</a></li>
    <li><a href="#key-features">Key Features</a></li>
    <li><a href="#architecture">Architecture</a></li>
    <li><a href="#future-enhancements">Future Enhancements</a></li>
    <li><a href="#contact">Contact</a></li>
  </ol>
</details>

---

<!-- ABOUT THE PROJECT -->
## About The Project

The **Atalanta A.C. API** is the backbone of the Atalanta e-commerce platform, built using PHP's popular framework **Laravel**. It provides powerful backend support, handling crucial operations such as order management, product creation, secure payments, and email automation.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

### Built With

-   [![Laravel][Laravel.com]][Laravel-url]
-   [![Filament][Filament.com]][Filament-url]
-   [![AWS]][AWS-url]
-   [![Stripe]][Stripe-url]

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Project Overview

The **Atalanta A.C. API** manages the following core responsibilities:

- **Admin Dashboard**: Built with **Filament**, providing a comprehensive admin dashboard to:
    - View and handle orders.
    - Manage users.
    - Create and update products, with images uploaded directly to **AWS S3**.
    - Modify homepage elements like the hero section and seasonal cards.

- **Email Management**: Automated email creation and delivery, powered by **Mailtrap**, for:
    - Email verification after registration.
    - Password reset emails.
    - Welcome emails for new users.
    - Order receipt emails.

- **Secure Payments**: Integrated with **Stripe API** to manage the heavy lifting of handling and securing user payment information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Key Features

1. **Comprehensive Admin Dashboard**  
   The API utilizes **Filament** to offer a sleek admin interface, where authorized administrators can manage:
    - Orders, including creating and updating products.
    - Users and roles.
    - Homepage customization, including hero banners and seasonal cards.
    - Image uploads to **AWS S3** for fast and secure storage.

2. **Automated Email Functionality**  
   Using **Mailtrap**, the API handles a variety of email processes:
    - Account verification emails post-registration.
    - Password reset emails.
    - Order confirmation and receipt emails sent to users post-purchase.
    - Welcome emails for newly registered users.

3. **Secure Payments with Stripe**  
   Orders are processed using **Stripe**, ensuring secure handling of users' personal and payment information. The API facilitates:
    - Payment processing.
    - Order creation and tracking.
    - Post-payment inventory updates in real-time.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Architecture

The **Atalanta A.C. API** is hosted and deployed using **Laravel Forge**, which seamlessly pushes changes from GitHub to AWS upon each commit. Key components include:

1. **Laravel Framework**
    - **Routing and Controllers**: Managing API endpoints for all backend processes, including product management, user authentication, and order handling.
    - **Models and Migrations**: Powering the database layer with **MySQL** for structured data storage.

2. **AWS Integration**
    - **AWS S3**: Manages product images and other assets for efficient and scalable storage.
    - **AWS RDS**: Powers the database, hosting all essential data for products, users, and orders.

3. **Stripe API**
    - **Payments**: Handles secure transactions, ensuring smooth order processing and updating stock levels once payments are confirmed.

4. **CI/CD Pipeline**
    - **Laravel Forge** deploys the API onto **AWS** with a simple "deploy on git commit" script, ensuring continuous integration with minimal manual intervention.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Future Enhancements

- **Graphs and Charts**: Add visual elements to the dashboard to track financial metrics, such as revenue flow over time.
- **Enhanced CMS Features**: Introduce more flexible content management tools, allowing admins to create frontend component blocks without needing to hard-code information.
- **Improved Inventory Management**: Implement a more efficient queue system to handle stock updates upon payment, offering a more robust inventory management experience.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

---

## Contact

**Henry Betancourth**
- Email: [noelys215@gmail.com](mailto:noelys215@gmail.com)
- GitHub Repo: [Atalanta Backend Repo](https://github.com/noelys215/atalanta_laravel)

<p align="right">(<a href="#readme-top">back to top</a>)</p>

[Laravel.com]: https://img.shields.io/badge/laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Filament.com]: https://img.shields.io/badge/Filament-056BFE?style=for-the-badge&logo=filament&logoColor=white
[Filament-url]: https://filamentphp.com
[AWS]: https://img.shields.io/badge/Amazon_AWS-FF9900?style=for-the-badge&logo=amazonaws&logoColor=white
[AWS-url]: https://aws.amazon.com/
[Stripe]: https://img.shields.io/badge/Stripe-626CD9?style=for-the-badge&logo=Stripe&logoColor=white
[Stripe-url]: https://stripe.com/
