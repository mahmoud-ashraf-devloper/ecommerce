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
       
## Note For paypal integration

The server side is done but there is some changes have to be done in the frontend.

You just need to put the paypal button and calling a method to create the order and another one to capture it and it's done, both end points are provided in the documentaion. 
