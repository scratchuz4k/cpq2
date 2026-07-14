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
const { getCurrentDate, generateHash, slugify } = require('../support/utils.js');
const temp = getCurrentDate();
const user = { name: slugify(temp), email: slugify(temp) + '@email.com', password: generateHash(temp) };

Cypress.Commands.add('login', (email, password) => {
    // run these tests as if in a desktop
    // browser with a 720p monitor
    cy.viewport(1920, 1080);
    cy.session([email, password, cy.id], () => {
        cy.visit('/login');
        cy.get('#email').type(email, { force: true });
        cy.get('#password').type(password, { force: true });
        cy.get('#btn-login').click();
        cy.get('#page-title').contains(/Quotes|Orders/);
    });
});

Cypress.Commands.add('selectLazyOption', (id, option = null) => {
    const inputSelect = `#input-select-${id}`;
    const optionsContainer = `#${id}-sidebar`;

    cy.get(inputSelect).click();
    cy.wait(200);
    if (option) {
        cy.get('#select-' + id + '-search')
            .clear()
            .type(option);
        cy.wait(1000);
    }
    const scrollToOption = (retries = 10) => {
        cy.get(optionsContainer).then(($container) => {
            const options = [...$container[0].querySelectorAll('.option')];

            const match = option ? options.find((el) => el.textContent.trim().includes(option)) : options[0];

            if (match) {
                cy.wrap(match).scrollIntoView().click();
            } else if (retries > 0) {
                cy.wrap($container)
                    .scrollTo('bottom')
                    .wait(300)
                    .then(() => {
                        scrollToOption(retries - 1);
                    });
            } else {
                throw new Error(`Option "${option}" not found in ${optionsContainer}`);
            }
        });
    };

    scrollToOption();
});

Cypress.Commands.add('selectOption', (id, option) => {
    cy.get(id).click();
    cy.wait(500);
    cy.get('.p-select-list-container').within(() => {
        cy.get('.p-select-option').contains(option).click();
    });
});

Cypress.Commands.add('selectFirstOption', (id) => {
    const inputSelect = `#input-select-${id}`;
    const optionsContainer = `#${id}-sidebar`;

    cy.get(inputSelect).click();
    cy.wait(200);

    const selectFirstOption = (retries = 10) => {
        cy.get(optionsContainer).then(($container) => {
            const options = [...$container[0].querySelectorAll('.option')];

            const firstOption = options[0];
            if (firstOption) {
                cy.wrap(firstOption).scrollIntoView().click();
            } else if (retries > 0) {
                cy.wrap($container)
                    .scrollTo('bottom')
                    .wait(300)
                    .then(() => {
                        selectFirstOption(retries - 1);
                    });
            } else {
                throw new Error(`No options found in ${optionsContainer}`);
            }
        });
    };

    selectFirstOption();
});

Cypress.Commands.add('createUser', (role) => {
    cy.visit('/users');
    cy.get('#link-to-create-user').click();

    cy.get('#user-name').type(user.name);
    cy.get('#user-email').type(user.email);
    cy.get('#user-password').type(user.password);
    cy.selectLazyOption('user-role', role);
    cy.get('#btn-save-user').click();
    cy.get('#datatable-search').type(user.name);
    cy.get('#users-table').contains(user.name);
});

Cypress.Commands.add('addProductAndVerifySummary', (selector, quoteItemsSelector = '.quote-items .product-name') => {
    cy.get(selector).first().closest('.panel').find('h3').invoke('text').as('productName');

    cy.get(selector).first().click();

    cy.get('@productName').then((name) => {
        cy.get(quoteItemsSelector).contains(name.trim()).should('exist');
    });
});

Cypress.Commands.add('newTableSearchAndEdit', (searchValue, searchSelector = '#datatable-search') => {
    cy.get(searchSelector).type(searchValue);
    cy.wait(1000);
    cy.get('.datatable-actions-trigger').first().click();
    cy.get('#datatable-btn-0-edit').click();
});

// The connectivity product table shows a "Finding your available products" modal while
// results are still streaming in. It blocks clicks on the filters underneath it, so dismiss
// it (via its "view now" link) if it's still up before interacting with the table.
Cypress.Commands.add('dismissLoadingModal', () => {
    cy.wait(300);
    cy.get('body').then(($body) => {
        if ($body.find('.loading-modal').length) {
            cy.get('.loading-modal').contains('a', 'view now').click();
        }
    });
});

Cypress.Commands.add('addProductToStep', (stepName, productName) => {
    cy.get('#settings').click();
    cy.get('#link-steps').click();
    cy.get('#datatable-search').type(stepName);
    cy.wait(1000);
    cy.get('#datatable-btn-0-edit').click();
    cy.get('#1-1').click();
    cy.selectLazyOption('select-product', productName);
    cy.get('#button-add-product-to-step').click();
    cy.get('.notyf').contains('Product added with Success');
    cy.get('.p-datatable-table').contains(productName);
});
