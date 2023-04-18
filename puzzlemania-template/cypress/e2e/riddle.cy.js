describe('Riddle API', () => {

    function createUsers(nUsers) {
        for (let i = 1; i <= nUsers; i++) {
            cy.visit("/sign-up");
            cy.get(`[data-cy="sign-up__email"]`).type(`student${i}@salle.url.edu`);
            cy.get(`[data-cy="sign-up__password"]`).type(`Test00${i}`);
            cy.get(`[data-cy="sign-up__repeatPassword"]`).type(`Test00${i}`);
            cy.get(`[data-cy="sign-up__btn"]`).click();
        }
    }

    function generateRiddlePerUserId(userId) {
        const riddle = {
            riddle: `Do you know about riddle #${userId}?`,
            answer: 'Of course!',
            userId: userId
        };
        return riddle;
    }

    function createRiddlePerUser(riddleForEachUser, nUsers) {
        let riddles = [];
        for (let u = 1; u <= nUsers; u++) {
            for (let p = 1; p <= riddleForEachUser; p++) {
                let riddle = generateRiddlePerUserId(u)
                let response = cy.request('POST', '/api/riddle', riddle)
                riddles.push({riddle: riddle, response: response});

            }
        }
        return riddles;
    }

    // This runs before each test
    beforeEach(() => {
        // recreate the database from schema.sql
        cy.recreateDatabase()
    })

    /**
     * CREATE
     */
    it('[R-1] adds a new riddle', () => {
        createUsers(1)

        let riddleWithUserId = generateRiddlePerUserId(1)

        cy.request('POST', '/api/riddle', riddleWithUserId)
            .then((response) => {
                expect(response.body).to.have.property('riddle', riddleWithUserId.riddle)

                expect(response.status).to.eq(201)
                expect(response.body).to.have.property('riddle', riddleWithUserId.riddle)
                expect(response.body).to.have.property('answer', riddleWithUserId.answer)
            })
    })

    it('[R-2] when creating a riddle, returns 400 if riddle or answer or userId keys are missing', () => {
        cy.request({method: 'POST', url: '/api/riddle', failOnStatusCode: false}, {})
            .then((response) => {
                const message = "'riddle' and/or 'answer' and/or 'userId' key missing";

                expect(response.status).to.eq(400)
                expect(response.body).to.have.property('message', message)
            })
    })

    /**
     * READ
     */
    it('[R-3] gets a JSON response', () => {
        cy.request('/api/riddle').its('headers').its('content-type').should('include', 'application/json')
    })

    it('[R-4] the riddle retrieved is the same as the riddle created', () => {
        createUsers(1)

        let riddle = createRiddlePerUser(1, 1)[0].response.then((response) => {
            let createdRiddle = response.body
            cy.request('GET', `/api/riddle/${createdRiddle.id}`).then((response) => {
                expect(response.status).to.eq(200)
                expect(response.body).to.have.property('id', createdRiddle.id)
                expect(response.body).to.have.property('userId', createdRiddle.userId)
                expect(response.body).to.have.property('riddle', createdRiddle.riddle)
                expect(response.body).to.have.property('answer', createdRiddle.answer)
            })
        })
    })

    it('[R-5] throws an error when requested for a non-existing riddle', () => {
        const message = "Riddle with id 1 does not exist"
        cy.request({url: `/api/riddle/1`, failOnStatusCode: false}).then((response) => {
            expect(response.status).to.eq(404)
            expect(response.body).to.have.property('message', message)
        })
    })

    it('[R-6] when creating 3 riddles, 3 riddles are retrieved', () => {
        createUsers(3)

        let responses = createRiddlePerUser(1, 3)

        cy.request('GET', `/api/riddle`).then((response) => {
            expect(response.body).to.have.lengthOf(3)
        })
    })

    /**
     * UPDATE
     */
    it('[R-7] updates a riddle', () => {
        createUsers(1)

        let riddle = createRiddlePerUser(1, 1)[0].response.then((response) => {
            let updatedRiddle = response.body
            updatedRiddle.riddle = 'Updated riddle'
            updatedRiddle.answer = 'Updated answer'

            cy.request('PUT', `/api/riddle/${updatedRiddle.id}`, updatedRiddle).then((response) => {
                expect(response.status).to.eq(200) // GET response code
                expect(response.body).to.have.property('riddle', updatedRiddle.riddle)
                expect(response.body).to.have.property('answer', updatedRiddle.answer)
            })
        })
    })

    it('[R-8] when updating a riddle, returns 400 if riddle or answer keys are missing', () => {
        createUsers(1)

        let riddle = createRiddlePerUser(1, 1)[0].response.then((response) => {
            let createdRiddle = response.body
            cy.request({method: 'PUT', url: `/api/riddle/${createdRiddle.id}`, failOnStatusCode: false, body: {}})
                .then((response) => {
                    const message = "'riddle' and/or 'answer' key missing";

                    expect(response.status).to.eq(400)
                    expect(response.body).to.have.property('message', message)
                })
        })
    })

    it('[R-9] when updating a riddle, returns 404 if the riddle is not found', () => {
        const message = "Riddle with id 18 does not exist"
        cy.request({
            method: 'PUT',
            url: `/api/riddle/18`,
            failOnStatusCode: false,
            body: generateRiddlePerUserId(1)
        }).then((response) => {
            expect(response.status).to.eq(404)
            expect(response.body).to.have.property('message', message)
        })
    })

    /**
     * DELETE
     */
    it('[R-10] deletes a riddle', () => {
        createUsers(1)
        let riddle = createRiddlePerUser(1, 1)[0].response.then((createResponse) => {
            cy.request('DELETE', `/api/riddle/${createResponse.body.id}`).then((response) => {
                const message = `Riddle with id ${createResponse.body.id} was successfully deleted`

                expect(response.status).to.eq(200)
                expect(response.body).to.have.property('message', message)
            })
        })
    })

    it('[R-11] throws an error when requested to delete a non-existing riddle', () => {
        const message = "Riddle with id 18 does not exist"
        cy.request({
            method: 'DELETE',
            url: `/api/riddle/18`,
            failOnStatusCode: false
        }).then((response) => {
            expect(response.status).to.eq(404)
            expect(response.body).to.have.property('message', message)
        })
    })
})
