import { faVoteYea } from "@fortawesome/free-solid-svg-icons";
import { generateHash, getCurrentDate } from "../../support/utils.js";

const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }
const price = { install: null, price: null }

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })

    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#save-account-button").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it('Get Price From Gamma Product', () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Connectivity - Ethernet & Fibre")
        cy.get("#input_address").type('EH32 9NT')
        cy.get("#select_address").should('be.visible').select(1)
        cy.get("#btn-create-quote").should('not.be.disabled').click()

        //Provider filter
        cy.get('#provider-multiselect').click()
        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
        })

        cy.wait(3000)

        cy.get("#line-0-price-install").then(($input) => {
            price.install = $input.text().trim();
            cy.log(price.install)
        })
        cy.get("#line-0-price-monthly").then(($input) => {
            price.monthly = $input.text().trim();
            cy.log(price.monthly)
        })
    })
    it('Create Supplier Gamma Products', () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-suppliers").click()
        cy.get("#datatable-search").type("Gamma")
        cy.wait(1000)
        cy.get('#suppliers-table').then(($table) => {
            if ($table.text().includes("No records found")) {
                cy.get("#create-suppliers").click()
                cy.get("#supplier-name").type("Gamma")
                cy.get("#supplier-reference").type("Gamma")
                cy.get("#save-supplier").click()
                cy.get('.p-row-even').should('have.length.greaterThan', 0);
            }
        });
    })
    it('Create SPA for Gamma Products', () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-spas").click()
        cy.get("#create-spa").click()
        cy.get("#spa-entity-Supplier").click()
        cy.get("#select-Supplier1").click()
        cy.get("#select-items-Supplier1 .select-item-0").eq(0).click()

        cy.get("#spa-percentage").type(20)
        cy.get("#save-spa").click()
        cy.get(".notyf").contains("Created with Success")

    })

    it('Recreate SPA for Gamma Products to check unique', () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-spas").click()
        cy.get("#create-spa").click()
        cy.get("#spa-entity-Supplier").click()
        cy.get("#select-Supplier1").click()
        cy.get("#select-items-Supplier1 .select-item-0").eq(0).click()

        cy.get("#spa-percentage").type(20)
        cy.get("#save-spa").click()
        cy.wait(2000)
        cy.get(".notyf").contains("There is alredy a SPA for that entity")

    })

    it('Check Price New Price For Gamma Product', () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-edit").click()

        //Provider filter
        cy.get('#provider-multiselect').click()
        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
        })

        cy.wait(3000)

        cy.get("#line-0-price-install").then(($input) => {
            expect($input.text().trim()).to.not.equal(price.install);
        });
        cy.get("#line-0-price-monthly").then(($input) => {
            expect($input.text().trim()).to.not.equal(price.monthly);
        });
    })

    it('Delete SPA ', () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-spas").click()
        cy.get("#datatable-btn-0-delete").click()
        cy.get("body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline").click()
    })

})
