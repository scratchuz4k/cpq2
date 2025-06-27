import { generateHash, getCurrentDate } from '../../support/utils.js';

const temp = getCurrentDate();
const category = { code: 'cat' + generateHash(temp), name: 'cat' + temp };
const category_2 = { code: 'cat-2' + generateHash(temp), name: 'cat-2' + temp };
const products = [
    { code: 'procuct-1' + generateHash(temp), name: 'Procuct 1' + temp },
    { code: 'procuct-2' + generateHash(temp), name: 'Procuct 2' + temp }
];
describe('Manage Products Category relation', () => {
    beforeEach(() => {
        cy.login('super@example.com', 'werwerwer');
    });

    it('Create a Category', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-categories').click();
        cy.get('#category-create-btn').click();
        cy.get('#category-code').type(category.code);
        cy.get('#category-name').type(category.name);
        cy.get('#btn-save-category').click();
        cy.get('#datatable-search').type(category.name);
        cy.get('.p-datatable-table').contains(category.name);

        cy.get('#category-create-btn').click();
        cy.get('#category-code').type(category_2.code);
        cy.get('#category-name').type(category_2.name);
        cy.get('#btn-save-category').click();
        cy.get('#datatable-search').type(category_2.name);
        cy.get('.p-datatable-table').contains(category_2.name);
    });

    products.forEach((product, index) => {
        it('Creates a Product', () => {
            cy.visit('/')
            cy.get('#my-account').click()
            cy.get('#link-products').click()

            cy.get('#product-create-btn').click()

            cy.get('#product-code').type(product.code)
            cy.get('#product-name').type(product.name)

            if (index === 1) {
                cy.selectLazyOption('product-categories', category.name);
            }

            cy.get('#btn-save-product').click()
            cy.get('.notyf').contains('Created with Success')

            cy.get('#datatable-search').type(product.code)
            cy.get('.p-datatable-table').contains(product.name);
        })
    })


    it('Check Category has related product', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-categories').click();
        cy.get('#datatable-search').type(category.name);
        cy.wait(1000)
        cy.get('#datatable-btn-0-edit').click()

        cy.get('#1-1').click();
        cy.get('.p-datatable-table').contains(products[1].name);

        cy.selectLazyOption('select-product', products[0].name);
        cy.get('#button-add-product-to-category').click();
        cy.get('.p-datatable-table').contains(products[0].name);
    });

    it('Check created products are not available to relate to another Category', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-categories').click();
        cy.get('#datatable-search').type(category_2.name);
        cy.wait(1000)
        cy.get('#datatable-btn-0-edit').click()

        cy.get('#1-1').click();

        products.forEach(product => {
            cy.get('#input-select-select-product').click()
            cy.get('#select-select-product-search').type(product.name)
            cy.wait(200)
            cy.get('#select-product-sidebar').contains('No available options')
            cy.get('#input-select-select-product').click()
        })
    });

    it('Remove Category Products', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-categories').click();
        cy.get('#datatable-search').type(category.name);
        cy.wait(1000)
        cy.get('#datatable-btn-0-edit').click()

        cy.get('#1-1').click();

        products.forEach(product => {
            cy.get('#datatable-search').clear().type(product.name);
            cy.wait(500)
            cy.get('#datatable-btn-0-delete').click()
            cy.get(".notyf").contains("Product removed with success");
        })
    });

    it('Check that Category has no Products', () => {
        cy.visit('/');
        cy.get('#my-account').click();
        cy.get('#link-categories').click();
        cy.get('#datatable-search').type(category.name);
        cy.wait(1000)
        cy.get('#datatable-btn-0-edit').click()

        cy.get('#1-1').click();

        products.forEach(product => {
            cy.get('#datatable-search').type(product.name);
            cy.wait(500)
            cy.get('.p-datatable-table').should('not.contain', product.name)
        })
    });
});
