Hi. FYI only.

I set myself a challenge to convert Dave Hollingworth's Registration and Login course, created over a decade ago (but updated), to work on his new MVC framework, released this year.
I do not say that I have understood all the issues as Dave's MVC course now deals with a number of new approaches including an awesome router based on pattern matching and capture groups, a container, dependency injection, php v 8+, middlewares and declare(strict_types=1) etc.
I still need to add in the javascript for the pages front end validation but have struggled with this (due to jquery and validate being a front end solution which could be slightly outdated).  For now, I would rather focus on the php validation which I believe is working.
The sql for the 3 tables is included in the repo and the ".env example" explains where you need to add in credentials etc.  I use Mailgun for the email solution.
My code is working at this moment and I can register a new user and receive notification via my email as well as update my password if it is forgotten.
My key challenge was working with the static methods in the Registration and Login course which I have converted to object methods using the constructor and dependency injection.  
I am hoping Dave will find the time to look at my effort and hoping for him to provide an upgrade to his codebase for Registration and Login that works with the new MVC.
I have added the final touches to the profile section (which is working) and I am working with the Admin interface (where user with is_admin = 1 in the mysql table can edit and update users in the system) which was added on afterwards by Dave but was not included in his course.  This, I believe, is working as well.  Now it is testing testing testing...

I love this framework as it is simple, based primarily on php and is very powerful in its simplicity and ability to teach me php, oop and have an easy no hassle web framework that can be secured.   

Anyway, long story short, the code I have is working but I am sure that many fixes will be required to make it 100% compatible with the new MVC framework.  I.e. I may have gone off the reservation to get a solution that works that bypassed the truth and essence of the new MVC framework. An example is the before() method in the Admin\Users.php controller.  I could not get this to work and believe I should use the new MVC middleware deny but resorted to:...  
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
Anyway.  Any assistance to point out my shortcomings and offer solutions will be greatly appreciated.

Mark.
