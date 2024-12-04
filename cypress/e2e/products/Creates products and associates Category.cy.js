import { generateHash, getCurrentDate } from "../../support/utils.js";

const temp1 = getCurrentDate()
const product = { code: generateHash(temp1), name: temp1 }

const temp2 = getCurrentDate()
const category = { code: generateHash(temp2), name: temp2 }

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })

    it("Creates a Category", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-categories").click()

        cy.get("#datatable-search").type(category.code)
        cy.wait(1000)
        cy.get('#categories-table').then(($table) => {
            if ($table.text().includes("No records found")) {
                cy.get("#category-create-btn").click()

                cy.get("#category-name").type(category.name)
                cy.get("#category-code").type(category.code)

                cy.get("#btn-save-category").click()
                cy.get(".notyf").contains("Created with Success")
            }
        });
    })

    it("Creates a Product and add category", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-products").click()

        cy.get("#datatable-search").type(product.code)
        cy.wait(1000)
        cy.get('#products-table').then(($table) => {
            if ($table.text().includes("No records found")) {
                cy.get("#product-create-btn").click()

                cy.get("#product-name").type(product.name)
                cy.get("#product-code").type(product.code)

                cy.get("#select-product-categories > div.p-multiselect-label-container > div").click()
                cy.get("#select-product-categories-search").type(category.code)
                cy.wait(1000)
                cy.get("#select-product-categories_0 > span").click()
                cy.get("#btn-save-product").click()
                cy.get(".notyf").contains("Created with Success")
            }
        });

        cy.get("#datatable-search").type(product.code)
        cy.wait(1000)
        cy.get("#datatable-btn-0-edit").click()
        cy.get("#select-product-categories > div.p-multiselect-label-container > div").contains(category.code)
    })

})
