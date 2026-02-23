const { getCurrentDate, generateHash } = require('../../support/utils.js');
const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber;
};

const temp = getCurrentDate();
const account = { ref: generateHash(temp), name: temp };
const provider = 'Giacom';
const subprovider = 'BT';

describe('Creates a Mobile Quote', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Create a Connectivity Quote', () => {
        cy.visit('/quotes/create');

        cy.selectFirstOption('account_id');
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-connectivity').click();
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#input_address').type('EH32 9NT');
        cy.selectOption('#select_address', 1);
        cy.get('.step-action-bar #btn-next-step').click();
        cy.get('#btn-create-quote').should('not.be.disabled').click();

        //Provider filter
        cy.get('#provider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains(provider).click();
            });

        //SubProvider filter
        cy.get('#subprovider-multiselect').click();
        cy.wait(500);
        cy.get('.p-multiselect-list-container')
            .eq(0)
            .within(() => {
                cy.get('.p-multiselect-option').contains(subprovider).click();
            });

        // Scroll back to top
        cy.scrollTo('top');
        cy.get('#table-header-setup').click();
        cy.wait(1000);
        cy.get('#table-header-setup').click();
        cy.wait(1000);

        //Add products to quote
        cy.get('tbody tr button').eq(0).click();
        cy.get('#connectivity-product-1').contains('Primary Product');

        //Save quote
        cy.get('#btn-continue').click();
        cy.get('.recurring-item').its('length').should('eq', 1);
        cy.get('.one_off-item').its('length').should('eq', 1);
    });
    it('Enable Activity recording', () => {
        cy.login('super@example.com', 'werwerwer');
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#1-1').click();
        cy.get('#log-system-is-active> input').then(($checkbox) => {
            if (!$checkbox.is(':checked')) {
                cy.wrap($checkbox).check();
                cy.get('.notyf').contains('Content updated successfully');
            }
        });
    });

    it('I can edit a line and check activity change', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();

        let items = ['recurring'];

        items.forEach((key) => {
            let total = null;
            cy.get('#' + key + '-total').then(($title) => {
                total = $title.text().trim();
            });

            let line1 = { qty: null, discount: null, margin: null };
            cy.get('#btn-' + key + '-0-edit').click();
            cy.get('#input-' + key + '-0-quantity')
                .then(($input) => {
                    line1.qty = $input.val().trim();
                })
                .clear()
                .type(rand(line1.qty));
            cy.get('#input-' + key + '-0-discount')
                .then(($input) => {
                    line1.discount = $input.val().trim();
                })
                .clear()
                .type(rand(0, 11));
            cy.get('#' + key + '-0-margin').then(($input) => {
                line1.margin = $input.text().trim();
            });

            cy.get('#btn-' + key + '-0-save').click();
            cy.wait(500);
            cy.get('#btn-' + key + '-0-edit').click();
            cy.get('#input-' + key + '-0-quantity')
                .invoke('val')
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.qty);
                });
            cy.get('#input-' + key + '-0-discount')
                .invoke('val')
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.discount);
                });
            cy.get('#btn-' + key + '-0-cancel').click();

            cy.get('#' + key + '-total')
                .invoke('text')
                .then((text) => {
                    expect(text).to.not.equal(total);
                });
        });
        cy.get('#btn-activity-open').click();
        cy.get('.p-timeline-event').should('have.length', 2);
    });

    it('Disabling Activity recording', () => {
        cy.login('super@example.com', 'werwerwer');
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-tenant').click();
        cy.get('#1-1').click();
        cy.get('#log-system-is-active> input').then(($checkbox) => {
            if ($checkbox.is(':checked')) {
                cy.wrap($checkbox).uncheck();
                cy.get('.notyf').contains('Content updated successfully');
            }
        });
    });
});
