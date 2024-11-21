import {generateHash, getCurrentDate, slugify} from "../../support/utils.js";

const temp = getCurrentDate()
const user = { name: slugify(temp), email: slugify(temp) + "@email.com", password: generateHash(temp) }

const models = [
    {
        name: 'Account',
        url: 'accounts',
        fields: [
            {
                id: '#account-name',
                type: 'text',
                value: 'Account' + getCurrentDate()
            },
            {
                id: '#account-reference',
                type: 'text',
                value: generateHash('Account' + getCurrentDate())
            }
        ]
    },
    {
        name: 'Brand',
        url: 'brands',
        fields: [
            {
                id: '#brand-name',
                type: 'text',
                value: 'Brand' + temp
            }
        ]
    },
    {
        name: 'Supplier',
        url: 'suppliers',
        fields: [
            {
                id: '#supplier-name',
                type: 'text',
                value: 'Supplier' + temp
            },
            {
                id: '#supplier-reference',
                type: 'text',
                value: generateHash('Supplier' + getCurrentDate())
            }
        ]
    },
    {
        name: 'Product',
        url: 'products',
        fields: [
            {
                id: '#product-name',
                type: 'text',
                value: 'Product' + getCurrentDate()
            },
            {
                id: '#supplier-name',
                type: 'select',
                value: 'Supplier' + temp
            },
            {
                id: '#brand-name',
                type: 'select',
                value: 'Brand' + temp
            },
            {
                id: '#product-code',
                type: 'text',
                value: generateHash('Brand' + temp)
            },
            {
                id: '#product-description',
                type: 'text',
                value: 'Brand' + temp
            }
        ]
    }
]

describe('Order Manager Permissions', () => {
    it('Create needed Content', () => {
        cy.login('super@example.com', 'werwerwer')
        models.forEach((model) => {
            cy.visit('/')
            cy.get("#my-account").click()
            cy.get(`#${model.url}-btn`).click()
            cy.get(`#${model.name.toLowerCase()}-create-btn`).click()

            model.fields.forEach((field) => {
                if (field.type === 'text') {
                    cy.get(field.id).type(field.value)
                } else {
                    cy.get(field.id).select(field.value)
                }
            })

            cy.get(`#btn-save-${model.name.toLowerCase()}`).click()
            cy.get("#data-table-search").type(model.fields[0].value)
            cy.get(".p-datatable-table").contains(model.fields[0].value)
        })
    })

    it("Creates Order Manager User", () => {
        cy.login('super@example.com', 'werwerwer')
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-users").click()
        cy.get("#link-to-create-user").click()
        cy.get("#user-name").type(user.name)
        cy.get("#user-email").type(user.email)
        cy.get("#user-password").type(user.password)
        cy.get("#user-role").select("Order Manager")
        cy.get("#btn-save-user").click()
        cy.get("#treetable-search").type(user.name)
        cy.get("#users-table").contains(user.name)
    })

    models.forEach((model) => {
        it(`View ${model.name}`, () => {
            cy.login(user.email, user.password)
            cy.visit(`/${model.url}`)
            cy.get('h1').contains(model.name)
        })

        it(`Edit ${model.name}`, () => {
            cy.login(user.email, user.password)
            cy.visit(`/${model.url}`)

            cy.intercept('GET', `/${model.url}/**/edit`).as(`edit${model.name}`);

            cy.get('#datatable-btn-0-edit').click()

            cy.wait(`@edit${model.name}`).then((interception) => {
                const statusCode = interception.response.statusCode;
                expect(statusCode).to.eq(403);
            })
        })

        it(`Delete ${model.name}`, () => {
            cy.login(user.email, user.password)
            cy.visit(`/${model.url}`)

            cy.get('#datatable-btn-0-delete').click()

            cy.intercept('DELETE', `api/${model.url}/**`).as(`delete${model.name}`);

            cy.get('body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline').click()

            cy.wait(`@delete${model.name}`).then((interception) => {
                const statusCode = interception.response.statusCode;
                expect(statusCode).to.eq(403);
            })
        })

        it(`Create ${model.name}`, () => {
            cy.login(user.email, user.password)
            cy.visit(`/${model.url}`)

            cy.intercept('GET', `create`).as(`create${model.name}`);

            cy.get(`#${model.name.toLowerCase()}-create-btn`).click()

            cy.wait(`@create${model.name}`).then((interception) => {
                const statusCode = interception.response.statusCode;
                expect(statusCode).to.eq(403);
            })
        })
    })
})
