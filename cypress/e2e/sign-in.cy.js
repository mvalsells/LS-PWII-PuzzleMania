describe("Sign in", () => {
    before(() => {
        cy.recreateDatabase();

        cy.visit("/sign-up");
        cy.get(`[data-cy="sign-up__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-up__password"]`).type("Test001");
        cy.get(`[data-cy="sign-up__repeatPassword"]`).type("Test001");
        cy.get(`[data-cy="sign-up__btn"]`).click();
    });

    it("[SI-1] shows the sign-in page", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in"]`).should("exist");
        cy.get(`[data-cy="sign-in__email"]`).should("exist");
        cy.get(`[data-cy="sign-in__password"]`).should("exist");
    });

    it("[SI-2] allows the user to sign-in correctly", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.location("pathname").should("eq", "/");
        cy.get(`[data-cy="home__welcomeMsg"]`).should("exist");
    });

    it("[SI-3] shows error when email does not have salle.url.edu", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@gmail.com");
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongEmail"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongEmail"]`)
            .invoke("text")
            .should("eq", "Only emails from the domain @salle.url.edu are accepted.");
    });

    it("[SI-4] shows error when email is not a valid email", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student");
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongEmail"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongEmail"]`)
            .invoke("text")
            .should("eq", "The email address is not valid.");
    });

    it("[SI-5] shows error when password has less than 7 characters", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-in__password"]`).type("Test");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongPassword"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongPassword"]`)
            .invoke("text")
            .should("eq", "The password must contain at least 7 characters.");
    });

    it("[SI-6] shows error when password does not follow correct format", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-in__password"]`).type("TestTest");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongPassword"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongPassword"]`)
            .invoke("text")
            .should(
                "eq",
                "The password must contain both upper and lower case letters and numbers."
            );
    });

    it("[SI-7] shows error when user does not exist", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type(
            "nicolemarie.jimenez@salle.url.edu"
        );
        cy.get(`[data-cy="sign-in__password"]`).type("Test001");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongEmail"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongEmail"]`)
            .invoke("text")
            .should("eq", "User with this email address does not exist.");
    });

    it("[SI-8] shows error when email and password do not match", () => {
        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type("student@salle.url.edu");
        cy.get(`[data-cy="sign-in__password"]`).type("Test002");
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__wrongPassword"]`).should("exist");
        cy.get(`[data-cy="sign-in__wrongPassword"]`)
            .invoke("text")
            .should("eq", "Your email and/or password are incorrect.");
    });

    it("[SI-9] shows email when password is incorrect", () => {
        let email = "student@salle.url.edu";
        let password = "p";

        cy.visit("/sign-in");
        cy.get(`[data-cy="sign-in__email"]`).type(email);
        cy.get(`[data-cy="sign-in__password"]`).type(password);
        cy.get(`[data-cy="sign-in__btn"]`).click();
        cy.get(`[data-cy="sign-in__email"]`).invoke('val').should("eq", email);
    });
});
