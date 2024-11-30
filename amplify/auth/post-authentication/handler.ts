import type { PostConfirmationTriggerHandler } from 'aws-lambda'
import {
    AdminAddUserToGroupCommand,
    CognitoIdentityProviderClient,
} from '@aws-sdk/client-cognito-identity-provider'

const client = new CognitoIdentityProviderClient()

export const handler: PostConfirmationTriggerHandler = async (event) => {
    const command = new AdminAddUserToGroupCommand({
        GroupName: 'USERS',
        UserPoolId: event.userPoolId,
        Username: event.userName,
    })

    await client.send(command)
    // console.log('processed', response.$metadata.requestId)
    return event
}
