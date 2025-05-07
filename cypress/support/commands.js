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
        cy.get('#page-title').contains(/Quotes|Orders/);
    }
    )
})

Cypress.Commands.add('selectLazyOption', (id, option) => {
    const inputSelect = `#input-select-${id}`;
    const optionsContainer = `#${id}-sidebar`;

    cy.get(inputSelect).click();

    const scrollToOption = (retries = 10) => {
        cy.get(optionsContainer).then($container => {
            if ($container.find(`div:contains("${option}")`).length) {
                cy.wrap($container)
                    .contains('div', option)
                    .scrollIntoView()
                    .click();
            } else if (retries > 0) {
                cy.wrap($container)
                    .scrollTo('bottom')
                    .wait(300)
                    .then(() => scrollToOption(retries - 1));
            } else {
                throw new Error(`Option "${option}" not found in #${id}-sidebar`);
            }
        });
    };

    scrollToOption();
})
