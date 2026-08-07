import { generateHash, getCurrentDate, slugify } from '../../support/utils.js';

const temp = getCurrentDate();
const group = { name: 'Group 1 ' + temp };
const sales_manager = {
    name: 'Sales Manager' + slugify(temp),
    email: slugify(temp) + '@example.com',
    password: generateHash(temp),
    role: 'Sales Manager'
};

const account = { ref: generateHash(temp), name: temp };
const group_account = { ref: 'group_account_' + generateHash(temp), name: 'Group Account -' + temp };
const group_user = {
    name: 'Group User -' + slugify(temp),
    email: 'group_user-' + slugify(temp) + '@example.com',
    password: generateHash(temp),
    role: 'Sales Rep'
};

describe('Group Testing', () => {
    beforeEach(() => {
        cy.login('companyadmin@example.com', 'werwerwer');
    });

    it('Create a Group', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#users-1-1').click();
        cy.get('#link-to-create-group').click();
        cy.get('#group-create-name').type(group.name, { force: true });
        cy.get('#group-create-submit').click();
        cy.get('.notyf').contains('Created with Success');
    });

    it('Creates an Account', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-accounts').click();
        cy.get('#link-to-create-account').click();
        cy.get('#account-name').type(account.name);
        cy.get('#account-reference').type(account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
        cy.get('#datatable-search').type(account.name);
        cy.get('.p-datatable-table').contains(account.name);
    });

    it('Creates Sales Manager User', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#link-to-create-user').click();
        cy.get('#user-name').type(sales_manager.name);
        cy.get('#user-email').type(sales_manager.email);
        cy.get('#user-password').type(sales_manager.password);
        cy.selectLazyOption('user-role', sales_manager.role);
        cy.get('#btn-save-user').click();
        cy.get('#datatable-search').type(sales_manager.name);
        cy.get('#users-table').contains(sales_manager.name);
        cy.get('#users-table').should('not.contain', group.name);
    });

    it("Check that there's no relation between created Group and user/account", () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#users-1-1').click();
        cy.get('#datatable-search').type(group.name);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#1-1').click();
        cy.get('#datatable-search').type(sales_manager.name, { force: true });
        cy.get('.p-datatable-table').should('not.contain', sales_manager.name);

        cy.get('#2-2').click();
        cy.get('#datatable-search').type(account.name, { force: true });
        cy.get('.p-datatable-table').should('not.contain', account.name);
    });

    it('Add Company Admin to a Group', () => {
        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#users-1-1').click();
        cy.get('#datatable-search').type(group.name);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#1-1').click();

        cy.selectLazyOption('select-user', sales_manager.name);
        cy.get('#button-add-user-to-group').click();
        cy.get('.notyf').contains('Created with Success');

        cy.get('#datatable-search').type(sales_manager.name, { force: true });
        cy.get('.p-datatable-table').contains(sales_manager.name);
    });

    it('Create Account with user that belongs to a Group', () => {
        cy.logout();
        cy.login(sales_manager.email, sales_manager.password);

        cy.visit('/quotes/create');
        cy.get('#link-to-create-account').click();
        cy.get('#account-name').type(group_account.name);
        cy.get('#account-reference').type(group_account.ref);
        cy.get('#btn-save-account').click();
        cy.get('.notyf').contains('Created with Success');
        cy.get('#datatable-search').type(group_account.name);
        cy.get('.p-datatable-table').contains(group_account.name);
    });

    it('Create User with user that belongs to a Group', () => {
        cy.logout();
        cy.login(sales_manager.email, sales_manager.password);

        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#link-to-create-user').click();
        cy.get('#user-name').type(group_user.name);
        cy.get('#user-email').type(group_user.email);
        cy.get('#user-password').type(group_user.password);
        cy.selectLazyOption('user-role', group_user.role);
        cy.get('#btn-save-user').click();

        cy.get('#datatable-search').type(group_user.name);
        cy.get('#users-table').contains(group_user.name);
        cy.get('#users-table').contains(group.name);
    });

    it('Check that new user/account are related to logged user Group', () => {
        cy.logout();
        cy.login(sales_manager.email, sales_manager.password);

        cy.visit('/quotes');
        cy.get('#settings').click();
        cy.get('#link-users').click();
        cy.get('#users-1-1').click();
        cy.get('#datatable-search').type(group.name);
        cy.get('#datatable-btn-0-edit').click();

        cy.get('#1-1').click();
        cy.get('#datatable-search').type(group_user.name, { force: true });
        cy.get('.p-datatable-table').contains(group_user.name);

        cy.get('#2-2').click();
        cy.get('#datatable-search').type(group_account.name, { force: true });
        cy.get('.p-datatable-table').contains(group_account.name);
    });
});
