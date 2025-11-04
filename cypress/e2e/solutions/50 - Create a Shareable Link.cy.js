const { getCurrentDate, generateHash } = require('../../support/utils.js');
const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
let url = '';

describe('Create a Shareable Link.cy', () => {
    beforeEach(() => {
        cy.login(Cypress.env('SUPERUSER_EMAIL'), Cypress.env('SUPERUSER_PASSWORD'));
    });
    it('Creates Mobile Quote', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectFirstOption('account_id');
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();
        cy.wait(1000);
        cy.get('#btn-plan-0-add-25').click();
        cy.wait(1000);
        cy.get('#btn-plan-1-add-25').click();
        cy.wait(1000);
        cy.get('#btn-step-next').click();
        cy.wait(1000);
        cy.get('#btn-plan-0-add-25').click();

        cy.wait(1000);
        cy.get('#btn-step-next').click();

        cy.get('#current-step')
            .invoke('text')
            .then((currentStepText) => {
                cy.get('#last-step')
                    .invoke('text')
                    .then((lastStepText) => {
                        const current_step = parseInt(currentStepText.trim());
                        const last_step = parseInt(lastStepText.trim());

                        for (let i = current_step; i < last_step; i++) {
                            cy.get(`#btn-step-next`).click();
                        }
                    });
            });

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
                    if (spanText > 0) {
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
        } while (hardwareCount == 0 && current_page <= max_pages);

        cy.get('#btn-step-next').click();
        cy.get('.recurring-item').should('have.length', 3);
        cy.get('.one_off-item').then(($items) => {
            cy.then(() => {
                expect(hardwareCount).to.equal($items.length);
            });
        });
    });
    it('I can create the link', () => {
        cy.visit('/');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#btn-shareable-open').click();
        cy.get('#btn-shareable-create').click();
        cy.get(`#input-shareable-link`)
            .invoke('val')
            .then((value) => {
                cy.log(value);
                url = value;
            });
    });

    it('I can access the link', () => {
        cy.logout();
        cy.visit('/');
        cy.log(url);
        cy.visit(url);
        cy.get('.item-row')
            .its('length')
            .then((items) => {
                expect(items).to.not.equal(0);
            });
    });
});
