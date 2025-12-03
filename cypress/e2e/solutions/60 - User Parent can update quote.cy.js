const { getCurrentDate, generateHash, slugify } = require('../../support/utils.js');
const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
const user = { name: slugify(temp), email: slugify(temp) + '@email.com', password: generateHash(temp) };
const managerPassword = 'werwerwer';
const managerUsers = ['sales@example.com', 'companyadmin@example.com'];

describe('User Parent can update a quote.cy', () => {
    it('Create a child user', () => {
        cy.login('companyadmin@example.com', 'werwerwer');
        cy.visit('/quotes');
        cy.get('#my-account').click();
        cy.get('#link-users').click();
        cy.get('#link-to-create-user').click();

        cy.get('#user-name').type(user.name);
        cy.get('#user-email').type(user.email);
        cy.get('#user-password').type(user.password);
        cy.selectLazyOption('user-role', 'Sales Rep');
        cy.get('#btn-save-user').click();
        cy.get('#datatable-search').type(user.name);
        cy.get('#users-table').contains(user.name);
    });

    it('Creates Mobile Quote', () => {
        //login child user and create a quote
        cy.login(user.email, user.password);
        cy.visit('/quotes');
        cy.get('#create-tab').click();
        cy.selectFirstOption('account_id');
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();

        let recurringCount = 0;
        cy.get('#current-step')
            .invoke('text')
            .then((currentStepText) => {
                cy.get('#last-step')
                    .invoke('text')
                    .then((lastStepText) => {
                        const current_step = parseInt(currentStepText.trim());
                        const last_step = parseInt(lastStepText.trim());

                        for (let i = current_step; i < last_step - 1; i++) {
                            cy.get('#btn-plan-0-add-25').click();
                            cy.wait(1000);
                            cy.get(`#next-step-btn`).click();
                            recurringCount += 1;
                        }
                        cy.get(`#next-step-btn`).click();
                    });
            });

        let hardwareCount = 0;
        let keys = [0];
        let max_pages = 2;
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

        cy.get('#next-step-btn').click();

        cy.get('.recurring-item').then(($items) => {
            cy.then(() => {
                expect(recurringCount).to.equal($items.length);
            });
        });
        cy.get('.one_off-item').then(($items) => {
            cy.then(() => {
                expect(hardwareCount).to.equal($items.length);
            });
        });
    });

    managerUsers.forEach((manager) => {
        it('Parent can update my quote', () => {
            cy.login(manager, managerPassword);
            cy.visit('/quotes');
            cy.wait(500);
            cy.get('#datatable-btn-0-show').click();
            let nrItems = 0;
            cy.get('.recurring-item').then(($items) => {
                cy.then(() => {
                    nrItems = $items.length;
                });
            });

            cy.get('#btn-recurring-add').click();
            cy.get('#input-new-recurring-name').type('Custom line recurring');
            cy.get('#input-new-recurring-quantity').type(10);
            cy.get('#input-new-recurring-price').type(10);
            cy.get('#input-new-recurring-discount').type(10);
            cy.get('#btn-new-recurring-add').click();
            cy.get('#table-recurring').contains('Custom line recurring');
            cy.wait(500);
            cy.get('.recurring-item').then(($items) => {
                cy.then(() => {
                    expect(nrItems + 1).to.equal($items.length);
                });
            });

            cy.get(`#btn-recurring-0-remove`).click();
            cy.get('#swal-btn-confirm').click();
            cy.wait(500);
            cy.get('.recurring-item').then(($items) => {
                cy.then(() => {
                    expect(nrItems).to.equal($items.length);
                });
            });
        });
    });
});
