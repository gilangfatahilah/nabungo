// UploadThing client types
// We use a simplified type definition to avoid complex FileRouter constraints

export type UploadThingFile = {
    url: string;
    key: string;
    name: string;
    size: number;
};

// Simple file router type that works with the client
export type OurFileRouter = Record<string, any>;
