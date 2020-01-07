USE [daiyas]
GO

/****** Object:  Table [dbo].[コーステーブル管理]    Script Date: 2020/01/06 16:10:09 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[コーステーブル管理](
	[部署ＣＤ] [int] NOT NULL,
	[コースＣＤ] [int] NOT NULL,
	[管理ＣＤ] [int] NOT NULL,
	[一時フラグ] [bit] NOT NULL,
	[適用開始日] [date] NOT NULL,
	[適用終了日] [date] NOT NULL,
	[備考] [nvarchar](50) NULL,
	[修正担当者ＣＤ] [int] NOT NULL,
	[修正日] [datetime] NOT NULL,
 CONSTRAINT [PK_コーステーブル管理] PRIMARY KEY CLUSTERED
(
	[部署ＣＤ] ASC,
	[コースＣＤ] ASC,
	[管理ＣＤ] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO


