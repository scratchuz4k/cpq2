const { getCurrentDate, generateHash, slugify } = require('../../support/utils.js');
const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
const user = { name: slugify(temp), email: slugify(temp) + '@email.com', password: generateHash(temp) };
const managerPassword = 'werwerwer';
const managerUsers = ['sales@example.com', 'companyadmin@example.com'];

describe('User Parent can update a quote.cy', () => {
    beforeEach(() => {
        cy.login('super@example.com', 'werwerwer');
    });
    it('Create a child user', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-users').click();
        cy.get('#link-to-create-user').click();

        cy.get('#user-name').type(user.name);
        cy.get('#user-email').type(user.email);
        cy.get('#user-password').type(user.password);
        cy.selectOption('#user-role', 'Sales Rep');
        cy.selectLazyOption('user-manager', 'Sales Rep');
        cy.get('#btn-save-user').click();
        cy.get('#treetable-search').type(user.name);
        cy.get('#users-table').contains(user.name);
    });

    it('Creates an Account', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.get('#link-to-create-account').click();
        cy.get('#account-name').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
    });

    it('Creates Mobile Quote', () => {
        //login child user and create a quote
        cy.logout();
        cy.login(user.email, user.password);
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();

        cy.get('#btn-plan-0-add-25').click();
        cy.wait(1000);
        cy.get('#btn-plan-1-add-25').click();
        cy.wait(1000);
        cy.get('#btn-step-1-next').click();
        cy.wait(1000);
        cy.get('#btn-plan-0-add-25').click();
        cy.wait(1000);
        cy.get('#btn-step-2-next').click();
        let hardwareCount = 0;
        let keys = [0, 1, 2, 3, 4];
        let max_pages = 5;
        let current_page = 1;

        do {
            keys.forEach((key) => {
                cy.get(`#hardware-${key}-stock`).then(($input) => {
                    const spanText = parseInt($input.text().trim()); // Get the text content from the <span> and trim any extra whitespace
                    const nameText = $input.closest('.panel').find('p.small').text().trim(); // Get the name of the product

                    // Perform conditional logic based on the span text and length of product name field test
                    if (spanText > 0 && nameText.length <= 100) {
                        hardwareCount += 1;
                        cy.log(`Click count: ${hardwareCount}`);
                        cy.get(`#input-hardware-${key}-quantity`).type(1);
                        cy.wait(1000);
                    }
                });
            });
            current_page += 1;
            cy.get('.p-paginator-page').contains(current_page).click();
            cy.wait(500);
        } while (hardwareCount === 0 && current_page <= max_pages);

        cy.get('#btn-step-2-next').click();

        cy.get('.recurring-item').should('have.length', 3);
        cy.get('.one_off-item').then(($items) => {
            cy.then(() => {
                expect(hardwareCount).to.equal($items.length);
            });
        });
    });

    managerUsers.forEach((manager) => {
        it('Parent can update my quote', () => {
            cy.logout();
            cy.login(manager, managerPassword);
            cy.visit('/');
            cy.wait(500);
            cy.get('#datatable-btn-0-edit').click();
            cy.wait(1000);
            cy.get('#btn-bolt-0-add-1').click();
            cy.wait(1000);
            cy.get('#btn-step-1-next').click();
            cy.wait(1000);
            cy.get('#btn-step-2-next').click();
            cy.wait(1000);
            cy.get('#btn-step-2-next').click();

            cy.get('.recurring-item').should('have.length', 3);

            cy.get('#btn-recurring-add').click();
            cy.get('#input-new-recurring-name').type('Custom line recurring');
            cy.get('#input-new-recurring-quantity').type(10);
            cy.get('#input-new-recurring-price').type(10);
            cy.get('#input-new-recurring-discount').type(10);
            cy.get('#btn-new-recurring-add').click();
            cy.get('#table-recurring').contains('Custom line recurring');

            cy.get('.recurring-item').should('have.length', 4);

            cy.get(`#btn-recurring-3-remove`).click();
            cy.get('#swal-btn-confirm').click();

            cy.get('.recurring-item').should('have.length', 3);
        });
    });
});
