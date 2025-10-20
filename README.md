# EOLAS - Educational AI Agent

Your AI-powered learning companion that adapts to you

## 🎯 Overview

EOLAS is an intelligent educational agent built with cutting-edge AWS AI services that provides personalized, adaptive learning experiences. The platform intelligently understands individual learning patterns and adjusts its teaching approach to match each student's unique pace and style.

## 🌟 Key Features

- **Personalized Learning Paths**: AI-driven adaptive learning that adjusts to individual student needs
- **Intelligent Query Understanding**: Leverages Amazon Bedrock AgentCore and Nova for natural language processing
- **Real-time Educational Assistance**: Immediate, contextual responses to student queries
- **Scalable Architecture**: Built on AWS Lambda for dynamic scaling and cost efficiency
- **Robust Data Management**: MySQL database for secure user profiles and progress tracking
- **Responsive Interface**: React Native frontend for seamless cross-platform experience

## 🏗️ Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    User Interface                           │
│                   (React Native App)                        │
└────────────────────────┬────────────────────────────────────┘
                         │ REST/GraphQL API
                         ↓
┌─────────────────────────────────────────────────────────────┐
│                 AWS API Gateway                             │
│              (Request Routing & Security)                   │
└────────────────────────┬────────────────────────────────────┘
                         │
              ┌──────────┴──────────┐
              ↓                     ↓
        ┌──────────────┐    ┌──────────────┐
        │ AWS Lambda   │    │ AWS Lambda   │
        │ Functions    │    │ Functions    │
        │ (Core Logic) │    │ (AI Handler) │
        └──────┬───────┘    └──────┬───────┘
               │                   │
               └──────────┬────────┘
                         ↓
        ┌────────────────────────────────────┐
        │  Amazon Bedrock AgentCore          │
        │  + Amazon Bedrock / Nova           │
        │  (AI Intelligence Layer)           │
        └─────────────────────────────────────┘
                         │
        ┌────────────────┴─────────────────┐
        │                                  │
        ↓                                  ↓
┌─────────────────────┐         ┌──────────────────┐
│  MySQL Database     │         │   Hostinger      │
│  (Hostinger)        │         │   Hosting        │
│                     │         │  (PHP5/MySQL     │
│ • User Profiles     │         │   Management)    │
│ • Learning Progress │         │                  │
│ • Educational       │         │  PHPMyAdmin      │
│   Content           │         │                  │
└─────────────────────┘         └──────────────────┘
```

### Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Frontend** | React Native | Responsive, cross-platform mobile interface |
| **API Layer** | AWS API Gateway | Secure request routing and rate limiting |
| **Compute** | AWS Lambda Functions | Serverless backend logic and processing |
| **AI/ML** | Amazon Bedrock AgentCore | Intelligent agent framework |
| **AI Models** | Amazon Bedrock / Nova | Advanced language understanding |
| **Database** | MySQL (Hostinger) | Persistent data storage |
| **Database UI** | PHPMyAdmin | Database administration and management |
| **Backend Support** | PHP5 | Legacy backend component support |
| **Security** | AWS IAM | Access control and authentication policies |
| **DevOps** | GitHub | Version control and deployment pipeline |

## 🛠️ Built With

- **AWS Services**
  - Amazon Bedrock AgentCore
  - Amazon Bedrock / Nova
  - AWS Lambda
  - AWS API Gateway
  - AWS IAM

- **Frontend**: React Native
- **Backend**: Python, PHP5
- **Database**: MySQL
- **Hosting**: Hostinger
- **Database Management**: PHPMyAdmin
- **Version Control**: GitHub

## 🚀 Getting Started

### Prerequisites

- AWS Account with Bedrock and Lambda access
- Node.js and npm (for React Native development)
- Python 3.x
- MySQL database credentials
- GitHub account for version control

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/shubhz1505/eolas-AI.git
   cd eolas-AI
   ```

2. **Install Frontend Dependencies**
   ```bash
   cd frontend
   npm install
   ```

3. **Configure AWS Services**
   - Set up IAM roles and policies for Lambda execution
   - Configure Bedrock AgentCore with your agent specifications
   - Set up API Gateway endpoints

4. **Database Setup**
   - Create MySQL database on Hostinger
   - Configure connection credentials
   - Initialize database schema using PHPMyAdmin

5. **Deploy Backend**
   - Package Lambda functions
   - Deploy to AWS using CLI or console
   - Configure environment variables

6. **Run Locally**
   ```bash
   npm start
   ```

## 📊 Data Flow

1. **User Interaction**: Student sends a query through React Native interface
2. **API Request**: REST/GraphQL API call sent to AWS API Gateway
3. **Authorization**: AWS IAM validates request credentials
4. **Lambda Processing**: API Gateway triggers appropriate Lambda function
5. **AI Processing**: Lambda invokes Bedrock AgentCore to understand query
6. **Database Query**: Lambda retrieves user context and learning history from MySQL
7. **Bedrock Response**: Bedrock/Nova generates contextual educational response
8. **Response Delivery**: Lambda returns response through API Gateway to frontend
9. **UI Update**: React Native interface displays personalized learning content

## 🔐 Security Architecture

- **Access Control**: AWS IAM policies restrict service-to-service communication
- **API Security**: API Gateway enforces authentication and rate limiting
- **Data Encryption**: MySQL database secured with credentials and SSL
- **Environment Isolation**: Separate configurations for development and production

## 🧪 Testing Instructions

### For Judges & Testers

1. **Visit the Application**
   - Navigate to: https://eolas.epictechglobal.com/

2. **Test Core Functionality**
   - Ask questions like: "Tell me about your backend and frontend functions"
   - Query topics relevant to educational content
   - Test adaptive responses based on question type

3. **Verify AI Agent Capabilities**
   - Submit multiple related queries to observe learning adaptation
   - Test different subject areas
   - Monitor response quality and relevance

4. **Check User Interface**
   - Verify responsiveness across different devices
   - Test navigation and user experience
   - Confirm data persistence between sessions

## 📈 Performance Considerations

- **Scalability**: Lambda auto-scales to handle multiple concurrent users
- **Response Time**: Optimized Bedrock queries for real-time assistance
- **Database Efficiency**: Indexed queries for fast user profile and progress retrieval
- **Cost Optimization**: Serverless architecture ensures pay-per-use model

## 🎓 Learning Path Adaptation

The AI agent adapts learning experiences through:
- Analysis of user query patterns
- Tracking of progress through topics
- Identification of knowledge gaps
- Personalized content recommendations
- Dynamic difficulty adjustment

## 🤝 Team & Contribution

This project was developed as a team submission for the AWS and DevPost AI Agent Global Hackathon.

**Submitter Type**: Team of Individuals

**Repository**: https://github.com/shubhz1505/eolas-AI.git

## 📝 License

This project is part of the AWS and DevPost AI Agent Global Hackathon competition.

## 🔗 Project Links

- **Live Application**: https://eolas.epictechglobal.com/
- **GitHub Repository**: https://github.com/shubhz1505/eolas-AI.git
- **Architecture Diagram**: See `/docs/architecture` directory

## 💡 Inspiration & Innovation

EOLAS addresses the critical gap in personalized education by recognizing that traditional one-size-fits-all educational platforms fail to cater to individual learning paces and styles. Our vision was to create an intelligent companion that makes learning more engaging and effective through adaptive, context-aware interactions powered by state-of-the-art AI.

## 🚧 Challenges Overcome

- **Service Integration**: Seamlessly integrated multiple AWS services with consistent data flow
- **Real-time AI Responses**: Fine-tuned Bedrock queries for optimal educational assistance
- **Hybrid Backend**: Successfully unified AWS Lambda with PHP5/MySQL ecosystem
- **API Security**: Implemented robust authentication and authorization across services

## 🎯 Future Enhancements

- Advanced analytics dashboard for learning insights
- Multi-language support for global accessibility
- Integration with popular educational content providers
- Enhanced offline capabilities for React Native app
- Advanced progress tracking and certification system

---

**Status**: New | **Start Date**: [09-25-2025] | **Hackathon**: AWS and DevPost AI Agent Global Hackathon