// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })


Cypress.Commands.add("login", (email, password) => {
    // run these tests as if in a desktop
    // browser with a 720p monitor
    cy.viewport(1920, 1080)
    cy.session([email, password, cy.id], () => {
        cy.visit("/login")
        cy.get("#email").type(email, { force: true })
        cy.get("#password").type(password, { force: true })
        cy.get("#btn-login").click()
        cy.get('#welcome-msg').contains('Welcome')
    },
        {
            cacheAcrossSpecs: true,
        }
    )
})
