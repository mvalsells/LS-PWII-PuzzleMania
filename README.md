# PuzzleMania

We've been too focused on learning fast and adopting new technological trends as Engineering students. It's time to
loosen up and start enjoying as well, but this doesn't mean we stop learning! Brainteasers and puzzles demand and afford
improvement of your
cognitive skills, helping you to enhance your ability to learn, your your hability to solve problems, and your memory as
you use specific brain regions. The Student Council asked for help from the creative students from Web Projects 2 to
implement
PuzzleMania.

## Introduction

PuzzleMania is a new platform where students can create puzzles &mdash; riddles, to be exact &mdash;, join a team and
start guessing riddles together with other students. To spice up the game, there will be a mystery puzzle to be solved!
It's better to work together with you teammates to find out. As they say:
> Together we stand, divided we fall.

## Pre-requisites and requirements

To be able to create this web app, you are going to need a local environment suited with:

1. Web server (Nginx)
2. PHP 8
3. MySQL
4. Barcode
5. Composer
6. Git

You have to use the Docker local-environment set up that we have been using in class. This time, you will find it
specifically tailored for this project in the project template `puzzlemania-template` (**inside the project template you will find a `README.md` with instructions on how to run the environment and how to run the tests**).

### Requirements

1. Use Slim as the underlying framework.
2. Create and configure services in the `dependencies.php` file. Examples of services are Controllers, Repositories, 'view', 'flash', ...
3. Use Composer to manage all the dependencies of your application. There must be at least two dependencies.
4. Use Twig as the main template engine.
5. Use CSS to add style to your application. Optionally, you may use a CSS framework. Keep the CSS simple, but do not use raw HTML without styling.
6. Use MySQL as the main database management system.
7. Use Git to collaborate with your teammates.
8. All the code must be uploaded to the private Bitbucket repository that has been assigned to your team.
9. You must use Namespaces, Classes, and Interfaces.
10. Each member of the team must collaborate in the project with at least 10 commits. Each member must commit, at least,
    code regarding the View (twig), the Controller, and the Model.

### Use of Artificial Intelligence (AI) assistants and tools - Disclosure
You cannot use artificial intelligence assistants to help you code, but you can use AI assistants to search and ask questions. For example, you can use Chat-GPT to ask questions. 

The style of allowed prompts is: "What are HTTP codes?", "How can I perform a redirect using HTTP?". The style of prompts that are not allowed is: "Create a user controller in Slim".

During the interview for the project, you will be required to disclose any AI usage during the project. Also, if we observe any suspicious code (e.g., the style and methods used are far from the ones used in the subject), we will ask you to provide the source/inspiration for that code, so make use of comments in your source code, please.

## Sections

_The stars next to each listed section indicate the estimated difficulty of that section (from 1 to 4)_

0. Register and Login (this is already done for you, including associated cypress tests)
1. Landing page (*)
2. Teams (**)
   1. Team stats (****)
4. Profile (***)
5. Game (****)
6. Riddles API (****)

### General considerations

* Every time you read "_The user must be logged in, and the functionality is **per user**._", if the user is not logged
  in, you have to redirect (**HTTP redirect**) the user to the landing page, sign up page, or sign in page, and you must
  show, unless otherwise stated, a generic message informing the user that they need to be authenticated to access that
  resource. For that, you must use **flash messages** (this is partially done for you using a middleware).
* The term "**redirect**" means HTTP redirect, which is not the same as to change the template you are rendering.

### Landing Page

_Anyone can view the landing page, even users who are not logged in._

This section describes the characteristics of the landing page of the application.

| Endpoints | Method |
| --------- | ------ |
| /         | GET    |

The landing page does not require user authentication. You need to implement a simple landing page where you will show a
brief description, the main features and functionalities of PuzzleMania. (This page does not show a list of all the
riddles in the system, it is an **informative** page.) Also, implement a navigation bar so the user can navigate through
the different functionalities of the project; some functionalities will not be available for users that have not been
authenticated: you have to deal with that issue visually too (e.g., hide the links or disable them). Do not use forms
with the method attribute set to GET. If you can use links, use them before unnecessary forms.

For this section, you will need to define a base template (twig inheritance) that is going to be used across all the
pages of the application. This template must contain at least the following blocks (**twig blocks**):

1. head
    - This contains the title and the meta information of the page.
2. styles
    - This is where you will load all the required CSS and/or other styles.
3. header
    - This contains the navigation menu. The navigation menu must contain buttons to sign in, sign up, and **links to other features of the application (profile, portfolio, ...)**. You do not have to disable or hide the buttons if the on the basis of an authenticated user (or not), but if the user is not allowed to access those links, the user must be redirected to the sign in or sign up page (as usual).
4. content
    - This is the main body of the webpage, depending on the feature being shown.

Feel free to add additional blocks as you consider necessary.

The qualification of this section includes the correct use of CSS and Semantic HTML for the whole project.

### Teams

_The user must be logged in, and the functionality is **per user**._

This section describes the team and all of its functionalities.

| Endpoints   | Method |
|-------------| ------ |
| /join       | GET    |
| /join       | POST   |
| /invite/join/{teamId}       | GET   |

If a user accesses **/join** and has already joined a team, they must be redirected to the **/team-stats** page
with a **flash message** saying that they have already joined a team. Otherwise, a form will be shown so the user can either join an existing team or create a new team (when a user creates a team, it becomes a member of that team automatically).

If there are any **incomplete** teams, the list of incomplete teams has to be shown, and the user must choose a team. An
incomplete team has less than or equal to 1 member. However, if there are no incomplete teams, the user must enter the
name of the new team. After the user submits the form, the team will be created and it will be available to other users
so that they can join.

The user will be redirected to **/team-stats** after joining or creating a team.

#### Team stats

| Endpoints   | Method |
|-------------| ------ |
| /team-stats | GET    |

If the user has not joined a team yet, you must redirect them **/join** page and show an informative **flash message**.

When the user accesses the **/team-stats** page, you must display the following information:

* Name of the team: alphanumeric
* Current number of team members: minimum of 1 and maximum of 2 members
* A list of team members: show the emails without the "@" part (i.e. for student1@salle.url.edu, you should only show
  "student1")
* Total number of points from last finished game

If the team is incomplete, there must be a button to generate a QR code using
the [Barcode API](https://www.neodynamic.com/products/barcode/docker/), this means that the QR code must encode the URL to join that team. This QR code encodes the
**/invite/join/{teamId}** URL, and, when accessed by another user, you must show a sign up page and if the user
successfully creates an account, they must be automatically added to the team. (Assume that the user that accesses that URL will not be authenticated, will not have an account, and the team will still be incomplete. 
). Once the QR code is generated, it should be displayed on the screen and a button should be added so it can be downloaded by the user and shared with other users; one the QR code is generated, the button to generate the QR code should disappear and the image should be displayed always.

**Tip**: You can use [HTTP callbacks](https://stackoverflow.com/questions/23347056/what-is-a-callback-url-in-relation-to-an-api) to implement this functionality and reuse code.

You can also browse the documentation for the Barcode API under the **barcode folder included in this project**. 
Notice that a docker service with the Barcode API has been included in the `docker-compose.yml`; **you must use this docker service to generate the QR code**.

**Warning:** keep an eye on the QR code images you generate, as they will start to take space on the remote repository if you save a lot of them in the server, and the repository may crash. Upload 2 QR images as maximum (if you need to, which depends on your implementation of the feature). All the images must be stored inside an "uploads" folder inside the public folder of the server in order to be
able to display them.

We will only play with a single user. However, we will validate that more than one user can join the same team, and the
**/team-stats** must show the same information for all users that belong to the same team.

### Profile

_The user must be logged in, and the functionality is **per user**._

This section describes the visualization and update of the user's personal information.

| Endpoints               | Method |
| ----------------------- | ------ |
| /profile                | GET    |
| /profile                | POST   |

When a logged user accesses to the **/profile** endpoint, you need to display a form containing the following inputs:

- email
- profile_picture

The email must be filled with the current stored information. The **email address cannot be updated** so the input must
be disabled.

The profile_picture must allow users to upload a profile picture. The requirements of the image are listed below:

1. The size of the image must be less than 1MB.
2. Only png and jpg images are allowed.
3. The image dimensions must be 400x400 (optionally, you can allow equal or less than 400x400). You can
   use [this service](https://dummyimage.com/) to create example images. Also, be careful to not commit images to the
   remote repository.
4. You need to generate a [UUID](https://github.com/ramsey/uuid) for the image and save it using the generated UUID as
   the image name (plus extension).

When the information is submitted, you need to validate the profile_picture. If there is any error, you need to
display them below the corresponding input.

All the images must be stored inside an "uploads" folder inside the public folder of the server in order to be
able to display them (`public/uploads/`).

### Game

_The user must be logged in, and the functionality is **per user**._

| Endpoints    | Method |
| ------------ | ------ |
| /game        | GET    |
| /game        | POST   |
| /game/{gameId}/riddle/{riddleId} | GET   |
| /game/{gameId}/riddle/{riddleId} | POST   |

When a user accesses **/game** without joining a group, the user will be redirected to **/join** page.

The Game section allows users to solve riddles. It has three main parts.

1. **Start of the game**. Shows the group name, instructions and a "Start" button. When the user clicks on the Start
   button, they will be redirected to **/game/{gameId}/riddle/{riddleId}**.

2. **During the game**. The user will try to guess the answers to 3 riddles.

3. **After the game**. The user will see the score of that game.

**Note**: The file [puzzlemania-template/resources/riddles/riddles.json](puzzlemania-template/resources/riddles/riddles.json) contains several riddles that you can add to the database manually or with `INSERT` queries. This may be useful for the *Riddles* section too.

#### Start of the game

Shows the group name, instructions and a "Start" button. When the user clicks on the Start button, they will be
redirected to **/game/{gameId}/riddle/{riddleId}**. Therefore, the "Start" button will send a POST request to **/game**,
this will create a game (generate a gameId) and save 3 riddles to solve. Then, the user will be redirected to **/game/{gameId}/riddle/1**, which will show the first riddle to solve.

**Tip**: Check out [rand() function](https://www.geeksforgeeks.org/php-rand-function/?ref=lbp). It may be useful for you to
implement this feature.

To start with the game, we're giving you a JSON file with 8 sample riddles. Note that it does not have the `userId`,
which is explained in the Riddles API section. This is because the riddles are NOT associated with any user. Therefore,
when you design and create your SQL code, the `userId` should accept NULL values. Insert these 8 sample riddles using
SQL code that will run when executing `docker compose up`.

#### During the game

You will show the riddle and a text field where the user will enter the answer, and there will be a button to submit the
answer to **/game/{gameId}/riddle/{riddleId}**. When the user submits the answer to a riddle, you must check if the
answer is correct or not, and you must show the result of the validation. In case it is not correct, you must show the
correct answer and a "Next" button to show the next riddle. In case it is correct, show the "Next" button too. The
"Next" button must link to the next riddle in the game, which, if the user was solving the first riddle in the game,
would be **/game/{gameId}/riddle/2**.

If it was the third or last riddle (0 points remaining, see below), you have to show a message informing the user that
it was the last riddle and show the total score of that game. You have also to show a "Finish" button. The "Finish" button must be a link to **/team-stats**.

#### Scoring system

1. The player starts with 10 points on each game for that game.
2. For each correct answer, the player will earn 10 points.
3. For each wrong answer, the player will lose 10 points.
4. The game will end when all riddles have been solved or if they use up all their points. If the player ends the game with more than 0 points, those points will be added to the team points.

### Riddles API

_The functionality is for all users._

_This functionality will be tested using cypress. You can check the tests in the cypress folder._

You must implement a REST API that allows the user to create a riddle, list all riddles, update a riddle and delete a
riddle.

The API specs are indicated in the [swagger file](puzzlemania-template/resources/barcode/swagger.json) in the `docs/` folder.  To visualize it, you can
copy the JSON, go to https://editor.swagger.io/ and paste it there. It is important that you **do not convert to YAML**
if the site asks you. There may be some inconsistencies and you might not be able to see the documentation correctly.

To learn more about Swagger, check out their documentation [here](https://swagger.io/docs/specification/about/).

**Webpages**

To access the webpages, the users must use the following endpoints:

| Endpoints     | Method |
| ------------- | ------ |
| /riddles      | GET    |
| /riddles/{id} | GET    |

The **/riddles** endpoint is a page where all the users can see a list of all the riddles created by all users that have
been
added to the blog. There must be an element with the id `riddles-list` that shows the list. For example:

```
<div id="riddles-list"><div>
```

However, if there are no entries, the element with `riddles-list` id must not exist in the DOM. In this case, another
element with the id `riddles-empty` that shows a message to indicate that there are no riddles.

To show a specific riddle, the user must access the **/riddles/{id}** endpoint.

**Tip**: You can check out [Random Riddles](https://www.riddles.nu/random) to have an idea of what riddles you can use to
test your API and application.

## Delivery

Since you are using Git, and also we want to make this project as real as possible, you are going to use annotated tags
in order to release new versions of your application. You can check the
official [git documentation](https://git-scm.com/book/en/v2/Git-Basics-Tagging) on how to create tags and use them.
Remember to push your tags to the Bitbucket repository, otherwise they will only stay in your local computer.

Also, remember to add the SQL code to the **docker-entrypoint-db** folder as a script. This will allow the automatic
creation of the database tables when running docker-compose.

There is only one graded delivery for this project. However, there will be a optional checkpoint were your teacher will review your project and guide you. You can check here the dates.

- Checkpoint between April 27 and May 4
- Final delivery May 21. Use **tag** `v1.0.0`

## Evaluation

1. To evaluate the project, we will use the release `v1.0.0` of your repository.
2. In May, all the teams that have delivered the final release on time, will be interviewed by the teachers.
3. In this interview we are going to validate that each team member have worked and collaborated as expected in the
   project.
4. Check the syllabus of the subject for further information.

#### Evaluation criteria

`v1.0.0`

To score the release `v1.0.0`, the distribution of points are as follows:

- Landing (Semantic HTML, CSS of the whole project): 0.5p
- Teams: 1p
- Team stats: 2p
- Profile: 1.5p
- Game: 3p
- Riddles API: 2p
- Other criteria (clean code quality, clean design,...): -1p
