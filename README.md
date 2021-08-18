## Ubucket
 > Unknown bucket(Ubucket) 

  Is an open source project which facilitate the project monitoring and easy up the development process through a web-based repository which both parties can keep track of their project and it show commit graph to any change done by contributors and owner in real time through this we can eliminate the problem of coders to keep track of their sources code changes.


### Audience

Target users of the project:
- Students
- Coders

  
## Technology used to implement ubucket
~~~
- Symfony framework (php-oriented)
- Git version control
- Redis server
- Putty for ssh connection
~~~ 
#### Make sure you install all technology for better test:
````
- git version control
- composer
- symfony cli
- redis server
- putty for ssh
- mysql server
````  
## Installation

### Git

1. Clone or download the project files;

        git clone https://github.com/l4f2s4/ubucket

2. Open project file using editor or git bash terminal:

        cd ubucket; 
3.composer magic

composer used to install package used on ubucket.

Inside ubucket terminal type

         composer install     

#### Load default database with the following command 

    php bin/console doctrine:database:create

    php bin/console doctrine:schema:create

    php bin/console doctrine:fixtures:load

### Default credential

````
   username:l4f2s4    
   password:ubucket
````

## Contribute To New Concept
Someone one think this is like github,bitbucket, and gitlab what new on this...

I create video code skeleton (you can find this on group area)  to introduce new concept on industries.   

For my taught i spent much time to look what things can help learner to learn software development in good way not only to see source code all those open sources 
don't implement real time learning chat where someone can correct another programmer code mistake
on early stage before project be public.

Let assume this concept it work like teamviewer collaborate tool. 


> I love contributors.

Do you have something to share? Want to help out with new concept? Create an issue or pull request on GitHub, 
or send 

an e-mail: lafesaofficial920@gmail.com.

You can also simply contribute to the project by _starring_ the project and show your appreciation that way.

Thanks!

### License

> GPLv3
