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

const temp1 = getCurrentDate();
const product = { sku: generateHash(temp1), name: temp1 };

const temp2 = getCurrentDate();
const step = { name: 'step-1' + temp2 };

describe('Creates a Mobile Quote', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Creates a Category', () => {
        cy.visit('/quotes');
        cy.get('#my-account').click();
        cy.get('#link-steps').click();

        cy.get('#datatable-search').type(step.name);
        cy.wait(1000);
        cy.get('#steps-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#step-create-btn').click();
                cy.get('#step-name').type(step.name);
                cy.selectOption('#step-solution-type', 'Mobile');
                cy.get('#btn-save-step').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates a Product and add step', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-products').click();

        cy.get('#datatable-search').type(product.sku);
        cy.wait(1000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();

                cy.get('#product-name').type(product.name);
                cy.get('#product-sku').type(product.sku);

                cy.selectLazyOption('product-steps', step.name);

                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates Mobile Quote', () => {
        cy.visit('/quotes');
        cy.get('#create-tab').click();
        cy.selectFirstOption('account_id');
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();
        cy.wait(1000);
        cy.get('#btn-product-0-add-25').click();
        cy.wait(1000);
        cy.get('#btn-product-1-add-25').click();
        cy.wait(1000);
        cy.get('#btn-step-next').click();
        cy.wait(1000);
        cy.get('#btn-product-0-add-25').click();
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
        let keys = [0, 1];

        cy.wait(2000);
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

            cy.get('.p-paginator-page')
                .contains(key + 1)
                .click();
            cy.wait(500);
        });

        cy.get('#btn-step-next').click();

        cy.get('.recurring-item').should('have.length', 3);
        cy.get('.one_off-item').then(($items) => {
            cy.then(() => {
                expect(hardwareCount).to.equal($items.length);
            });
        });
    });

    it('I can change Margin and Discount', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();

        let items = ['recurring', 'one_off'];

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

            cy.get('#' + key + '-total')
                .invoke('text')
                .then((text) => {
                    expect(text).to.not.equal(total);
                });
        });
    });

    it('I can change and reset Global Discount', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();
        let quote = { margin: null, commission: null, global: null };

        cy.get('#input-margin-order').then(($input) => {
            quote.margin = $input.val().trim();
        });
        cy.get('#input-global-MONTHLY-discount')
            .then(($input) => {
                quote.global = $input.val().trim();
            })
            .clear()
            .type(rand(quote.global));

        cy.get('#btn-apply-global-discount').click();
        cy.get('#btn-apply-global-discount', { timeout: 3000 }).should('not.be.disabled');

        cy.get('#input-margin-order')
            .invoke('val')
            .then((val) => {
                expect(val).to.not.equal(quote.margin);
            });

        cy.get('#btn-reset-quote').click();
        cy.get('#swal-btn-confirm').click();
        cy.get('#btn-reset-quote', { timeout: 3000 }).should('not.be.disabled');

        cy.get(`#recurring-discount-0`)
            .invoke('text')
            .then((text) => {
                expect(text).to.not.equal('0 %');
            });
    });

    it('I can add and delete a Custom Line', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-show').click();
        cy.get('#btn-recurring-add').click();
        cy.get('#input-new-recurring-name').type('Custom line recurring');
        cy.get('#input-new-recurring-quantity').type(10);
        cy.get('#input-new-recurring-price').type(10);
        cy.get('#input-new-recurring-discount').type(10);
        cy.get('#btn-new-recurring-add').click();
        cy.get('#table-recurring').contains('Custom line recurring');

        cy.get('#btn-one_off-add').click();
        cy.get('#input-new-one_off-name').type('Custom line one_off');
        cy.get('#input-new-one_off-quantity').type(10);
        cy.get('#input-new-one_off-price').type(10);
        cy.get('#input-new-one_off-discount').type(10);
        cy.get('#btn-new-one_off-add').click();
        cy.get('#table-one_off').contains('Custom line one_off');

        cy.get('.recurring-item')
            .its('length')
            .then((count) => {
                cy.get(`#btn-recurring-${count - 1}-remove`).click();
            });
        cy.get('#swal-btn-confirm').click();
        cy.get('.one_off-item')
            .its('length')
            .then((count) => {
                cy.get(`#btn-one_off-${count - 1}-remove`).click();
            });
        cy.get('#swal-btn-confirm').click();
    });
});
