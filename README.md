Hi.

I set myself a challenge to convert Dave Hollingworth's Registration and Login course, created a while ago, to work on his new MVC framework, released this year.
I do not say that I have understood all the issues as his MVC course now deals with a number of new approaches including an awesome router based on pattern matching and capture groups, a container, dependency injection, php v 8+ and declare(strict_types=1) etc.
My code is working at this moment and I can register a new user and receive notification via my email as well as update my password if it is forgotten.
My key challenge was working with the static methods in the Registration and Login course which I have converted to object methods using the constructor and dependency injection.  
I am hoping Dave will find the time to look at my effort and hoping for him to provide an upgrade to his codebase for Registration and Login that works with the new MVC.
I am currently busy adding the final touches to the profile section (which is working) and I am currently struggling with the Admin panel which was added on afterwards by Dave but was not included in his course.

I love this framework as it is simple, based primarily on php and very powerful in its simplicity and ability to teach me php, oop and have a web framework that can be secured.   
I still need to add in the javascript fine tuning for the pages but have struggled with this (due to jquery and validate being a front end solution is dated) and for now, I would rather focus on the php validation which I believe is working.
The sql for the 3 tables is included and the .env example explains where you need to add in credentials etc.  I use Mailgun for the email solution.
Anyway, long story short, the code I have is working but I am sure that many fixes will be required to make it 100% compatible with the new MVC framework.

Mark.
