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
        cy.location('pathname')
            .should('eq', '/dashboard')
    },
        {
            cacheAcrossSpecs: true,
        }
    )
})

Cypress.Commands.add("additems", (name) => {
    cy.get("#create-tab").click()
    cy.get("#account_id").select(name)
    cy.get("#solution_id").select("Mobile")
    cy.get("#btn-create-quote").click()
    cy.get("#btn-plan-0-add-25").click()
    cy.get("#btn-plan-1-add-25").click()
    cy.get("#btn-step-1-next").click()
    cy.get("#btn-step-2-previous").click()
    cy.get("#btn-plan-2-add-50").click()
    cy.get("#btn-step-1-next").click()
    cy.get("#input-bolt-0-quantity").type(30)
    cy.get("#btn-step-2-next").click()
    let hardwareCount = 0
    let keys = [0, 1, 2, 3, 4]
    keys.forEach((key) => {
        cy.get(`#hardware-${key}-stock`).then(($input) => {
            const spanText = parseInt($input.text().trim()); // Get the text content from the <span> and trim any extra whitespace

            // Perform conditional logic based on the span text
            if (spanText > 0) {
                hardwareCount += 1
                cy.log(`Click count: ${hardwareCount}`);
                cy.get(`#input-hardware-${key}-quantity`).type(spanText)
            }
        });
    })

    cy.get("#btn-step-3-next").click()

    cy.get(".recurring-item").should("have.length", 4)
    cy.get(".one_off-item").then(($items) => {
        cy.then(() => {
            expect(hardwareCount).to.equal($items.length);
        });
    })
})
