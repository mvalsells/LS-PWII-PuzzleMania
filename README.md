# PuzzleMania - A simple riddle game

## Description
A simple web app to guest riddles and score points with friends and teams of up to two people. The app was developed for a university project.

## Deployment
1. Clone the repository
2. Run `docker compose up` [1]
3. Run `composer install` in the root folder
4. Open a browser and go to `localhost:8030` to access the app
5. Have fun!

**NOTE:** The `public` directory must be writable by the `app`and `nginx` containers in order to create folders and files for the profile pictures and teams QR codes. 

**[1]** If you are using an Apple Silicon Mac, you may need to run `docker compose -f docker-compose.yaml -f docker-compose.arm.yaml up` instead of `docker compose up`.

## Authors
- Marc Valsells Niubó
- Òscar de Jesús Ruiz
- David Larrosa Camps