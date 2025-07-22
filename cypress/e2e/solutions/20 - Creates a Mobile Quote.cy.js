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
const product = { code: generateHash(temp1), name: temp1 };

const temp2 = getCurrentDate();
const category = { code: generateHash(temp2), name: temp2 };

describe('Creates a Mobile Quote', () => {
    beforeEach(() => {
        cy.login('super@example.com', 'werwerwer');
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

    it('Creates a Category', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-categories').click();

        cy.get('#datatable-search').type(category.code);
        cy.wait(3000);
        cy.get('#categories-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#category-create-btn').click();

                cy.get('#category-name').type(category.name);
                cy.get('#category-code').type(category.code);

                cy.get('#btn-save-category').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });
    });

    it('Creates a Product and add category', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-products').click();

        cy.get('#datatable-search').type(product.code);
        cy.wait(3000);
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes('No records found')) {
                cy.get('#product-create-btn').click();

                cy.get('#product-name').type(product.name);
                cy.get('#product-code').type(product.code);

                cy.selectLazyOption('product-categories', category.name);

                cy.get('#btn-save-product').click();
                cy.get('.notyf').contains('Created with Success');
            }
        });

        cy.get('#datatable-search').type(product.code);
        cy.wait(3000);
        cy.get('#datatable-btn-0-edit').click();

        cy.selectLazyOption('product-categories', category.name);
    });

    it('Associate Category with Mobile Solution', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-solutions').click();
        cy.get('#datatable-search').type('Mobile');
        cy.wait(3000);
        cy.get('#datatable-btn-0-edit').click();
        cy.get('#1-1').click();

        cy.get('#solution-type-table-add-category').click();
        cy.wait(500);
        cy.selectLazyOption('solution-type-categories-0-value', 'Plans');

        cy.get('#solution-type-table-add-category').click();
        cy.selectLazyOption('solution-type-categories-1-value', category.name);
        cy.get('#btn-save-solution-type').click();
    });

    it('Creates Mobile Quote', () => {
        cy.visit('/');
        cy.get('#create-tab').click();
        cy.selectLazyOption('account_id', account.name);
        cy.get('#btn-next-step').click();

        cy.get('#solution_type-mobile').click();
        cy.get('#btn-create-quote').click();
        cy.wait(1000);
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

        cy.get('#btn-step-2-next').click();

        cy.get('.recurring-item').should('have.length', 3);
        cy.get('.one_off-item').then(($items) => {
            cy.then(() => {
                expect(hardwareCount).to.equal($items.length);
            });
        });
    });

    it('I can change Margin and Discount', () => {
        cy.visit('/');
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
        cy.visit('/');
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
        cy.get(
            'body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline'
        ).click();
        cy.get('#btn-reset-quote', { timeout: 3000 }).should('not.be.disabled');

        cy.get(`#recurring-discount-0`)
            .invoke('text')
            .then((text) => {
                expect(text).to.not.equal('0 %');
            });
    });

    it('I can add and delete a Custom Line', () => {
        cy.visit('/');
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

        cy.get(
            'body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline'
        ).click();
        cy.get('.one_off-item')
            .its('length')
            .then((count) => {
                cy.get(`#btn-one_off-${count - 1}-remove`).click();
            });
        cy.get(
            'body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline'
        ).click();
    });

    it('I can Create an order from a quote', () => {
        cy.visit('/');
        cy.get('#datatable-btn-0-crete-order').click();
        cy.get('.swal2-confirm').eq(0).click();
        cy.get('.notyf').contains('Order created with success');
    });
});
