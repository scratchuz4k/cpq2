const { getCurrentDate, generateHash } = require('../../support/utils.js');
const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber;
};

const temp1 = getCurrentDate();
const product = { sku: generateHash(temp1), name: temp1 };

const temp2 = getCurrentDate();
const step = { name: 'step-1' + temp2 };

const temp3 = getCurrentDate();
const addonProduct = { sku: generateHash(temp3 + '-addon'), name: 'Addon ' + temp3 };

describe('Creates a Mobile Quote', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Creates a Step', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
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
        cy.get('#settings').click();
        cy.get('#link-products').click();

        cy.get('#datatable-search').type(product.sku);
        cy.wait(1000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();

                cy.get('#product-name').type(product.name);
                cy.get('#product-sku').type(product.sku);

                cy.selectLazyOption('product-steps', 'Plans');

                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates Mobile Quote', () => {
        cy.visit('/quotes/create');
        cy.selectFirstOption('account_id');
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();
        cy.get('#btn-product-0-add-1').click();
        cy.get('#btn-product-1-add-1').click();
        cy.wait(1000);

        cy.get('#current-step')
            .invoke('text')
            .then((currentStepText) => {
                cy.get('#last-step')
                    .invoke('text')
                    .then((lastStepText) => {
                        const current_step = parseInt(currentStepText.trim());
                        const last_step = parseInt(lastStepText.trim());

                        for (let i = current_step; i < last_step; i++) {
                            cy.get(`#btn-step-${i}-next`).click();
                        }
                    });
            });

        let hardwareCount = 0;
        let keys = [0, 1];

        cy.wait(1000);
        keys.forEach((key) => {
            cy.get(`#hardware-${key}-stock`).then(($input) => {
                const spanText = parseInt($input.text().trim()); // Get the text content from the <span> and trim any extra whitespace
                const nameText = $input.closest('.panel').find('p.small').text().trim(); // Get the name of the product

                // Perform conditional logic based on the span text and length of product name field test
                if (spanText > 0) {
                    hardwareCount += 1;
                    cy.log(`Click count: ${hardwareCount}`);
                    cy.get(`#input-product-${key}-quantity`).type(1);
                    cy.wait(1000);
                }
            });

            cy.get('.p-paginator-page')
                .contains(key + 1)
                .click();
            cy.wait(500);
        });

        cy.get('.step-action-bar #btn-step-next').click();

        cy.get('.recurring-item').should('have.length', 2);
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
            cy.scrollTo('top', { duration: 500 });
            let total = null;
            cy.get('#' + key + '-total').then(($title) => {
                total = $title.text().trim();
            });

            cy.wait(200);
            let line1 = { qty: null, discount: null, margin: null };
            cy.get('#btn-' + key + '-0-edit').click();
            cy.wait(200);
            cy.get('#input-' + key + '-0-quantity')
                .then(($input) => {
                    line1.qty = $input.val().trim();
                })
                .clear()
                .type(rand(line1.qty));
            cy.wait(200);
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
            cy.get('#input-' + key + '-0-quantity', { force: true })
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
        cy.scrollTo('top', { duration: 500 });

        cy.get('#btn-MONTHLY-edit').click();

        cy.get('#input-global-MONTHLY-discount')
            .then(($input) => {
                quote.global = $input.val().trim();
            })
            .clear()
            .type(rand(quote.global));

        cy.get('#btn-MONTHLY-save').click({ force: true });

        cy.wait(1000);

        cy.get('#input-margin-order')
            .invoke('val')
            .then((val) => {
                expect(val).to.not.equal(quote.margin);
            });

        cy.get('#btn-MONTHLY-remove').click();
        cy.get('#swal-btn-confirm').click();

        cy.get(`#recurring-discount-0`)
            .invoke('text')
            .then((text) => {
                expect(text).to.not.equal('0 %');
            });
    });

    it('Creates an addon product and add it to the main product', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-products').click();

        cy.get('#datatable-search').type(addonProduct.sku);
        cy.wait(1000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();
                cy.get('#product-name').type(addonProduct.name);
                cy.get('#product-sku').type(addonProduct.sku);
                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });

        cy.get('#datatable-search').clear().type(product.sku);
        cy.wait(1000);
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#tabs-product-edit-3-3').click();
        cy.get('#btn-add-addons').click();
        cy.get('#addons-search-global').type(addonProduct.sku);
        cy.get('#addons-table').should('not.contain', 'Loading');
        cy.get('#addons-table').contains(addonProduct.sku);
        cy.get('[id^="addons-checkbox-"]').first().click({ force: true });
        cy.get('#btn-relation-add-addons').click({ force: true });
        cy.get('.notyf').contains('Products added successfully');
        cy.get('#btn-relation-cancel-addons').click();
        cy.wait(1000);
        cy.get('#product-addons-datatable-search').clear().type(addonProduct.sku);
        cy.get('#product-addons').should('not.contain', 'Loading');
        cy.get('#product-addons').contains(addonProduct.sku);
    });

    it('Adds an addon to the Mobile quote and verifies it is in the quote', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#step-1-search-products').clear().type(product.sku);
        cy.wait(1000);
        cy.get(`#input-product-0-quantity`).clear().type(1);

        cy.get('[id^="btn-addon-"][id$="-add-1"]').should('be.visible');
        // The product with the newly linked addon should be visible in step 1
        // Product is already in the quote (qty > 0), so the addon section is shown
        cy.get('[id^="btn-addon-"][id$="-add-1"]').first().click();

        // Verify the addon quantity was incremented to 1
        cy.get('[id^="input-addon-"][id$="-quantity"]').first().should('have.value', '1');

        cy.get('#current-step')
            .invoke('text')
            .then((currentStepText) => {
                cy.get('#last-step')
                    .invoke('text')
                    .then((lastStepText) => {
                        const current_step = parseInt(currentStepText.trim());
                        const last_step = parseInt(lastStepText.trim());

                        for (let i = current_step; i < last_step; i++) {
                            cy.get(`#btn-step-${i}-next`).click();
                        }
                    });
            });

        // Complete the quote flow and verify the addon appears in the summary
        cy.get('.step-action-bar #btn-step-next').click();
        cy.get('.recurring-item, .one_off-item').should('exist');

        cy.get('.recurring-item, .one_off-item').then(($items) => {
            const names = [...$items].map((el) => el.textContent);
            expect(names.some((text) => text.includes(addonProduct.name))).to.be.true;
        });
    });
});
