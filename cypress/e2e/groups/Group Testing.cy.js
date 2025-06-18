import {generateHash, getCurrentDate, slugify} from "../../support/utils.js";

const temp = getCurrentDate();
const group = { name: 'Group 1 ' + temp };
const sales_manager = { name: 'Sales Manager' + slugify(temp), email: slugify(temp) + "@example.com", password: generateHash(temp), role: 'Sales Manager'}


const account = { ref: generateHash(temp), name: temp };
const updated_account = { ref: account.ref + '_updated', name: account.name + '_updated'};

describe("Group Testing", () => {
    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer");
    });

    // it("Create a Group", () => {
    //     cy.visit("/");
    //     cy.get("#my-account").click();
    //     cy.get("#link-users").click();
    //     cy.get("#1-1").click();
    //     cy.get("#link-to-create-group").click();
    //     cy.get("#group-create-name").type(group.name, { force: true })
    //     cy.get("#group-create-submit").click()
    //     cy.get(".notyf").contains("Created with Success")
    // });
    //
    // it("Creates an Account", () => {
    //     cy.visit("/");
    //     cy.get("#create-tab").click();
    //     cy.get("#link-to-create-account").click();
    //     cy.get("#account-name").type(account.name);
    //     cy.get("#account-reference").type(account.ref);
    //     cy.get("#btn-save-account").click();
    //     cy.get(".notyf").contains("Created with Success");
    //     cy.get("#datatable-search").type(account.name);
    //     cy.get(".p-datatable-table").contains(account.name);
    // });

    // it("Creates Sales Manager User", () => {
    //     cy.visit("/");
    //     cy.get("#my-account").click();
    //     cy.get("#link-users").click();
    //     cy.get("#link-to-create-user").click()
    //     cy.get("#user-name").type(sales_manager.name)
    //     cy.get("#user-email").type(sales_manager.email)
    //     cy.get("#user-password").type(sales_manager.password)
    //     cy.get("#user-role").select(sales_manager.role)
    //     cy.get("#btn-save-user").click()
    //     cy.get("#treetable-search").type(sales_manager.name)
    //     cy.get("#users-table").contains(sales_manager.name)
    //     cy.get("#users-table").should('not.contain', group.name)
    // })

    it("Check that there's no relation between created Group and user/account", () => {
        cy.visit("/");
        cy.get("#my-account").click();
        cy.get("#link-users").click();
        cy.get("#1-1").click();
        cy.get("#datatable-search").type(group.name);
        cy.get("#datatable-btn-0-edit").click()


        cy.get("#1-1").click();
        cy.get("#datatable-search").type(sales_manager.name);
        cy.get(".p-datatable-table").should('not.contain', sales_manager.name);

        cy.get("#2-2").click();
        cy.wait(1000);
        cy.get("#datatable-search").type(account.name);
        cy.get(".p-datatable-table").should('not.contain', account.name);
    })
});
