const product = {
    sku: 'e8f81a67-bd96-4074-b108-cf193eb9433b',
    name: 'Product EXCHANGE_S_ESSENTIALS'
};
const productPrice = {
    code: 1,
    cost: 10,
    price: 10
};

describe('Match CouldAudit Sku', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Create a CouldAudit Quote', () => {
        cy.visit('/quotes/create');

        cy.selectFirstOption('account_id');
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#solution_type-cloud_audit').click();
        cy.get('.step-action-bar #btn-next-step').click();

        cy.wait(500);
        cy.get('input[type="radio"]').click({ force: true });
        cy.get('.step-action-bar #btn-next-step').click();

        cy.get('#table-simple tbody tr').each(($row) => {
            // Price column (5rd column)
            cy.wrap($row)
                .find('td')
                .eq(4)
                .invoke('text')
                .then((text) => {
                    expect(text.trim()).to.equal('N/A');
                });

            // Total Price column (6th column)
            cy.wrap($row)
                .find('td')
                .eq(5)
                .invoke('text')
                .then((text) => {
                    expect(text.trim()).to.equal('N/A');
                });
        });
    });

    it('Create a product with the same sku', () => {
        cy.visit('/');
        cy.get('#settings').click();
        cy.get('#link-products').click();
        cy.get('#product-create-btn').click();

        cy.get('#product-sku').type(product.sku);
        cy.get('#product-name').type(product.name);

        cy.get('#price_plan_1').click();
        cy.get('#contract_term_value').type('100');
        cy.selectOption('#contract_term_unit', 'Days');

        cy.get('#add-price-btn').click();

        cy.get('#price-code').type(productPrice.code);
        cy.get('#price-cost').type(productPrice.cost);
        cy.get('#price-price').type(productPrice.price);

        cy.get('.p-dialog .btn.btn-primary').click();
        //cy.get('.swal2-confirm').eq(0).click();
        cy.get('#btn-save-product').click();
        cy.get('.notyf').contains('Created with Success');
    });

    it('Check CouldAudit quote prices', () => {
        cy.visit('/quotes');
        cy.get('#datatable-btn-0-edit').click();

        cy.contains('#table-simple tbody tr td', product.name)
            .parents('tr')
            .find('td')
            .then(($cells) => {
                const qty = parseInt($cells.eq(1).text().trim().replace('x', ''));
                const price = parseFloat($cells.eq(4).text().replace(/[£,]/g, '').trim());
                const totalPrice = parseFloat($cells.eq(5).text().replace(/[£,]/g, '').trim());
                expect(price).to.eq(productPrice.price);
                expect(totalPrice).to.eq(qty * price);
            });
    });
});
