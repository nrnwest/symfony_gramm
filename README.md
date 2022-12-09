## Project Names

Storyline:
Write a program like Instagram. This application must have a web interface.
The user can register on the website by email.After basic registration,
the user will receive a confirmation of the continuation of registration.
The email must have a unique link. The user goes by a link that should be redirected to the profile
page and add his
full name, bio, and avatar. Next user can use SymfonyGramm. He can post images,
look at pictures of other users. Unauthorized guests
cannot view the profile and pictures of users.

The project needs to be pinked on a Linux or Windows (wsl2 Unbuntu 20.*) platform.
Docker must be installed.

### Web Admin login

login: userAdmin@example.com  
password: 123654  
панель: /admin

### Deployment

```bash
git clone https://github.com/nrnwest/symfony_gramm.git
```

```bash
make dep
````

If an error occurs, run the following commands, errors occur due to weak computer:

1. make clear_migrate
2. make build
3. make up
3. make composer
4. make clear_add
5. make t_clear_add

### Website

<http://localhost:5000>

### pgAdmin

user admin@admin.com
password root
<http://localhost:5050/browser/>

### Tests

```bash
make test 
````
