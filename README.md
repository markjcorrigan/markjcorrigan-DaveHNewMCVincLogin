Hi.  About this work. FYI only.

I set myself a challenge to convert Dave Hollingworth's Registration and Login course, created over a decade ago (but updated regularly), to be able to work on his new MVC framework, released this year.
I do not say that I have understood all the issues as Dave's MVC course now deals with a number of new approaches including an awesome router based on pattern matching and capture groups, a container, dependency injection, php v 8+, middlewares and declare(strict_types=1) etc.
I still need to add in the javascript for the pages for front end validation but have struggled with this (due to jquery and validate being a front end solution which could be slightly outdated).  
For now, I would rather focus on the php validation which I believe is working.

The sql for the 4 tables is included in the repo and the ".env example" explains where you need to add in credentials etc.  I use Mailgun for the email solution.
My code is working at this moment and I can register a new user and receive notification via my email as well as update my password if it is forgotten.
If you test this remember that you need to go to composer.json and install phpmailer.  An email will be sent out to a legitimate email address so if you make one up you will not be able to receive it and test the functionality.
It does work and I have tested this through my normal email and my gmail accounts.
I also use the twig templating engine which is added per composer.json.  I.e. copy the code from this repo and then recreate the vendor folder using the command composer install.

My key challenge was working with the static methods in the Registration and Login course which I have converted to object methods using the constructor in each class and dependency injection.  
I am hoping Dave will find the time to look at my effort and grade it for me with advice.  I also hope Dave will eventually provide an upgrade to his codebase for Registration and Login that works with the new MVC, and that I can look at this and compare.
I have just added the final touches to the profile section (which is working).  This is where a registered user can update their details.
I also finished the Admin interface (where user with is_admin = 1 in the mysql table can edit and update users in the system) which was added by Dave on request from his students but was not included in his course.  
I have also added a search area where a user (or the admin) can put in page name's descriptions url's and keywords.  I.e. use this interface to map the web and search for content.  Middleware is used to protect the serach routes as you will see and only is_admin is true can use the routes that edit, delete the pages.
These, I believe, are all working.  Now it is testing testing testing...

I love this framework as it is simple, based primarily on pure php and is very powerful in its ability to teach php (oop) and is an easy no hassle web framework that can be quickly put into place and secured.  With the container and dependency injection etc, using the framework as a web developer, we are all the better for it.

Anyway, long story short, the code I have seems to be up and running.  However, I am sure that many fixes will be required to make it 100% compatible with the new MVC framework.  I.e. I may have gone off the reservation to get a solution that works that bypassed the truth and essence of the new MVC framework and approach that was envisaged by Dave. An example is the before() method in the Admin\Users.php controller.  I could not get this to work no matter what I did.
I also believed I should use the new MVC middleware deny (or isadmin) but finally, with a heavy heart, resorted to:...  
 ```
   protected function requireAdmin(): void
        {
            $user = $this->auth->getUser();
            if (!$user || !$user->is_admin) {
                Flash::addMessage('You are not allowed to access that resource.', Flash::WARNING);
                header('Location: /');
                exit;
            }
        }  
```

A final note:  I have included many of the classes from the Register and Login course into the Framework folder.  The only change I have made to the Framework was a very small change in the Dispatcher class.  For that I apologise to Dave.  I could not figure out a way around it.  I have left the original code commented out.
I will have to rationalize the older classes but that will need to happen at another time.

Anyway.  Any assistance (updates to the code via pull requests or discussion) to point out my shortcomings and offer solutions will be greatly appreciated.

Keep well.

Mark.
