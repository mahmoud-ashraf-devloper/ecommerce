## instalation 
* git clone "git@github.com:mahmoud-ashraf-devloper/ecommerce.git"
* composer install
* add your database creds
* php artisan key:generate
* php artisan migrate:fresh --seed
* php arisan passport:install
    - You will have an admin and normal user seeded into the database
        * user
            email: user@user.com
            password : password
       * admin
            email: admin@admin.com
            password : password
       
